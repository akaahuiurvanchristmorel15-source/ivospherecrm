<?php

use App\Http\Controllers\Commercial\CyberDepositDeskController;
use App\Models\CyberPrintRequest;
use App\Models\User;
use App\Services\Cyber\DocumentPageCounter;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    // Comptage déterministe : sans LibreOffice
    config(['services.libreoffice.binary' => false]);
    Storage::fake('local');

    $this->admin = User::where('email', 'admin@ivosphere.com')->firstOrFail();
});

function depositUrl(): string
{
    test()->actingAs(test()->admin)->get(route('commercial.cyber.qr'))->assertOk();
    $url = route('cyber.deposit.show', CyberDepositDeskController::uploadToken());
    auth()->logout();

    return $url;
}

function pdfUpload(string $name, int $pages): UploadedFile
{
    $html = implode('<div style="page-break-after: always">page</div>', array_fill(0, $pages, ''));

    return UploadedFile::fake()->createWithContent($name, Pdf::loadHTML($html.'<div>fin</div>')->output());
}

function docxUpload(string $name, int $pages): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'docx');
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body/></w:document>');
    $zip->addFromString('docProps/app.xml', '<?xml version="1.0"?><Properties><Pages>'.$pages.'</Pages></Properties>');
    $zip->close();

    return UploadedFile::fake()->createWithContent($name, (string) file_get_contents($path));
}

test('the counter reads the page tree of a pdf', function () {
    $counter = new DocumentPageCounter;
    $content = Pdf::loadHTML(implode('<div style="page-break-after: always">x</div>', array_fill(0, 4, '')).'<div>y</div>')->output();

    expect($counter->countPdfPages($content))->toBe(4);
});

test('a client sends documents from the qr code and gets a pickup code with counted pages', function () {
    $url = depositUrl();

    $this->get($url)->assertOk()->assertSee('Envoyez vos documents à imprimer');

    $this->post($url, [
        'customer_name' => 'Awa Koné',
        'customer_phone' => '07 08 09 10 11',
        'copies' => 2,
        'color' => 1,
        'duplex' => 0,
        'files' => [pdfUpload('rapport.pdf', 3), docxUpload('lettre.docx', 2)],
    ])->assertRedirect($url)->assertSessionHas('deposit');

    $deposit = CyberPrintRequest::withoutGlobalScopes()->with('files')->firstOrFail();

    expect($deposit->files->pluck('pages', 'original_name')->all())->toBe(['rapport.pdf' => 3, 'lettre.docx' => 2])
        ->and($deposit->pagesPerCopy())->toBe(5)
        ->and($deposit->totalPages())->toBe(10)
        ->and($deposit->color)->toBeTrue()
        ->and($deposit->customer_phone)->toBe('+225 07 08 09 10 11')
        ->and(strlen($deposit->code))->toBe(4);

    Storage::disk('local')->assertExists($deposit->files->first()->path);
    $this->get($url)->assertSee($deposit->code);
});

test('only pdf, word and excel documents are accepted', function () {
    $url = depositUrl();

    $this->post($url, [
        'customer_name' => 'Awa Koné',
        'copies' => 1,
        'files' => [UploadedFile::fake()->create('virus.exe', 10)],
    ])->assertSessionHasErrors('files.0');

    expect(CyberPrintRequest::withoutGlobalScopes()->count())->toBe(0);
});

test('an unknown or regenerated qr code no longer accepts documents', function () {
    $this->get(route('cyber.deposit.show', str_repeat('x', 40)))->assertNotFound();

    $url = depositUrl();
    $this->actingAs($this->admin)->post(route('commercial.cyber.qr.regenerate'))->assertRedirect(route('commercial.cyber.qr'));
    auth()->logout();

    $this->get($url)->assertNotFound();
});

test('the cyber desk lists deposits, opens files and updates their status', function () {
    $url = depositUrl();
    $this->post($url, ['customer_name' => 'Koffi', 'copies' => 1, 'files' => [pdfUpload('cours.pdf', 2)]]);
    $deposit = CyberPrintRequest::withoutGlobalScopes()->with('files')->firstOrFail();

    $this->getJson(route('commercial.cyber.deposits.index'))->assertUnauthorized();

    $this->actingAs($this->admin)
        ->getJson(route('commercial.cyber.deposits.index'))
        ->assertOk()
        ->assertJsonPath('deposits.0.code', $deposit->code)
        ->assertJsonPath('deposits.0.total_pages', 2)
        ->assertJsonPath('deposits.0.files.0.pages', 2);

    $this->actingAs($this->admin)
        ->get(route('commercial.cyber.deposits.download', [$deposit, $deposit->files->first()]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $this->actingAs($this->admin)
        ->patchJson(route('commercial.cyber.deposits.status', $deposit), ['status' => 'retire'])
        ->assertOk();

    expect($deposit->fresh()->status)->toBe('retire');

    $this->actingAs($this->admin)->get(route('commercial.cyber.index'))->assertOk()->assertSee('Documents reçus');
});
