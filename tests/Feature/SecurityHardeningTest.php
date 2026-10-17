<?php

use App\Models\Document;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Subscriptions\CinetPayGateway;
use App\Services\Subscriptions\SubscriptionService;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    Storage::fake('public');
    Storage::fake('local');

    $this->admin = User::where('email', 'admin@ivosphere.com')->first();
    $this->agent = User::factory()->create(['is_active' => true]);
    $this->agent->roles()->sync([Role::where('slug', 'agent_commercial_terrain')->value('id')]);
});

test('login is locked after five wrong passwords', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => 'admin@ivosphere.com', 'password' => 'mauvais'])->assertSessionHasErrors('email');
    }

    $this->post('/login', ['email' => 'admin@ivosphere.com', 'password' => 'password'])->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toStartWith('Trop de tentatives de connexion.');
    $this->assertGuest();
});

test('the GED refuses executable and scriptable files', function (string $fileName, string $content) {
    $this->actingAs($this->agent)
        ->post(route('ged.documents.store'), ['title' => 'Piège', 'file' => UploadedFile::fake()->createWithContent($fileName, $content)])
        ->assertSessionHasErrors('file');

    expect(Document::count())->toBe(0);
})->with([
    'php' => ['shell.php', '<?php echo "pwned";'],
    'html' => ['page.html', '<html><script>alert(1)</script></html>'],
    'svg' => ['image.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
]);

test('GED files are stored privately and only served through the open route', function () {
    $this->actingAs($this->agent)
        ->post(route('ged.documents.store'), ['title' => 'Contrat', 'file' => UploadedFile::fake()->create('contrat.pdf', 50, 'application/pdf')])
        ->assertSessionHasNoErrors();

    $document = Document::firstOrFail();

    Storage::disk('local')->assertExists($document->file_path);
    Storage::disk('public')->assertMissing($document->file_path);
    $this->get(route('ged.documents.open', $document))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('the vault is reserved to the direction', function () {
    Storage::disk('local')->put('vault_files/bilan.pdf', '%PDF-1.4');
    $secret = Document::create([
        'title' => 'Bilan', 'file_name' => 'bilan.pdf', 'file_path' => 'vault_files/bilan.pdf', 'file_size' => 8,
        'mime_type' => 'application/pdf', 'user_id' => $this->admin->id, 'is_vault' => true, 'version' => '1.0', 'status' => 'active',
    ]);

    $this->actingAs($this->agent);
    $this->get(route('ged.vault'))->assertForbidden();
    $this->get(route('ged.documents.open', $secret))->assertForbidden();
    $this->delete(route('ged.documents.destroy', $secret))->assertForbidden();
    $this->post(route('ged.documents.store'), ['title' => 'Intrus', 'is_vault' => 1, 'file' => UploadedFile::fake()->create('x.pdf', 5, 'application/pdf')])
        ->assertForbidden();

    $this->actingAs($this->admin)->get(route('ged.documents.open', $secret))->assertOk();
});

test('a document can only be deleted by its author or the direction', function () {
    $document = Document::create([
        'title' => 'Note', 'file_name' => 'note.pdf', 'file_path' => 'ged_files/note.pdf', 'file_size' => 8,
        'mime_type' => 'application/pdf', 'user_id' => $this->admin->id, 'is_vault' => false, 'version' => '1.0', 'status' => 'active',
    ]);

    $this->actingAs($this->agent)->delete(route('ged.documents.destroy', $document))->assertForbidden();
    expect(Document::find($document->id))->not->toBeNull();

    $this->actingAs($this->admin)->delete(route('ged.documents.destroy', $document))->assertRedirect();
    expect(Document::find($document->id))->toBeNull();
});

test('a subscriber name cannot inject script into the Ivosphere administration', function () {
    $tenant = Tenant::create([
        'name' => '<script>alert("xss")</script>', 'slug' => 'piege', 'email' => 'p@p.ci', 'phone' => '07',
        'subscription_plan_id' => SubscriptionPlan::where('code', 'commercial_caisse')->value('id'), 'status' => 'pending',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.subscriptions.show', $tenant))
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('&lt;script&gt;', false);
});

test('the payment simulation is never available in production', function () {
    config(['subscriptions.payment.provider' => 'simulation']);
    app()->detectEnvironment(fn () => 'production');

    expect(app(SubscriptionService::class)->gateway())->toBeInstanceOf(CinetPayGateway::class);

    $tenant = Tenant::create(['name' => 'Boutique', 'slug' => 'boutique', 'email' => 'b@b.ci', 'phone' => '07', 'status' => 'pending',
        'subscription_plan_id' => SubscriptionPlan::where('code', 'commercial_caisse')->value('id')]);
    $subscription = Subscription::create(['tenant_id' => $tenant->id, 'subscription_plan_id' => $tenant->subscription_plan_id,
        'billing_cycle' => 'monthly', 'amount' => 15000, 'currency' => 'XOF', 'status' => 'pending', 'payment_provider' => 'simulation']);

    $this->get(route('offres.payment.simulation', $subscription))->assertNotFound();
});
