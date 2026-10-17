<?php

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\User;
use Database\Seeders\DomainSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DomainSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(UserSeeder::class);

    Storage::fake('public');
    Storage::fake('local');
    $this->actingAs(User::where('email', 'admin@ivosphere.com')->first());

    $this->contracts = DocumentFolder::create(['name' => 'Contrats', 'slug' => 'contrats', 'color' => '#0066FF', 'is_vault' => false]);
    $this->clients = DocumentFolder::create(['name' => 'Clients 2026', 'slug' => 'clients-2026', 'color' => '#10B981', 'parent_id' => $this->contracts->id, 'is_vault' => false]);
});

function gedDocument(string $title, ?int $folderId, string $fileName = 'fichier.pdf'): Document
{
    return Document::create([
        'title' => $title, 'file_name' => $fileName, 'file_path' => 'ged_files/'.$fileName, 'file_size' => 2048,
        'mime_type' => 'application/pdf', 'folder_id' => $folderId, 'user_id' => auth()->id(), 'is_vault' => false, 'version' => '1.0', 'status' => 'active',
    ]);
}

test('folders open without reloading and show their content and breadcrumb', function () {
    gedDocument('Contrat cadre Orange', $this->contracts->id);

    Livewire::test('ged-explorer')
        ->assertSee('Contrats')
        ->assertDontSee('Contrat cadre Orange')
        ->call('openFolder', $this->contracts->id)
        ->assertSet('folderId', $this->contracts->id)
        ->assertSee('Contrat cadre Orange')
        ->assertSee('Clients 2026')
        ->call('openFolder', $this->clients->id)
        ->assertSee('Ce dossier est vide');
});

test('search looks through every folder and the type filter narrows it', function () {
    gedDocument('Facture fournisseur papier', $this->clients->id, 'facture.pdf');
    gedDocument('Photo vitrine', null, 'vitrine.jpg');
    gedDocument('Facture scannée', null, 'scan.png');

    Livewire::test('ged-explorer')
        ->set('search', 'facture')
        ->assertSee('Facture fournisseur papier')
        ->assertSee('Facture scannée')
        ->assertDontSee('Photo vitrine')
        ->set('type', 'image')
        ->assertSee('Facture scannée')
        ->assertDontSee('Facture fournisseur papier');
});

test('dropped files are archived in the current folder with a readable title', function () {
    Livewire::withQueryParams(['dossier' => $this->contracts->id])
        ->test('ged-explorer')
        ->set('uploadTags', 'client, 2026')
        ->set('uploads', [UploadedFile::fake()->create('contrat_orange-final.pdf', 300), UploadedFile::fake()->image('logo.png')])
        ->assertHasNoErrors()
        ->assertDispatched('ged-toast');

    $document = Document::where('file_name', 'contrat_orange-final.pdf')->firstOrFail();

    expect(Document::count())->toBe(2)
        ->and($document->title)->toBe('Contrat orange final')
        ->and($document->folder_id)->toBe($this->contracts->id)
        ->and($document->tags)->toBe(['client', '2026']);
    Storage::disk('local')->assertExists($document->file_path);
    Storage::disk('public')->assertMissing($document->file_path);
});

test('a new folder is created inside the folder being viewed', function () {
    Livewire::withQueryParams(['dossier' => $this->contracts->id])
        ->test('ged-explorer')
        ->set('newFolderName', 'Avenants')
        ->call('createFolder')
        ->assertHasNoErrors()
        ->assertSee('Avenants');

    expect(DocumentFolder::where('name', 'Avenants')->value('parent_id'))->toBe($this->contracts->id);
});

test('deleting a document also removes its file', function () {
    Storage::disk('public')->put('ged_files/a-supprimer.pdf', 'contenu');
    $document = gedDocument('À supprimer', null, 'a-supprimer.pdf');

    Livewire::test('ged-explorer')->call('deleteDocument', $document->id);

    expect(Document::find($document->id))->toBeNull();
    Storage::disk('public')->assertMissing('ged_files/a-supprimer.pdf');
});

test('a document can be opened or downloaded, and a missing file gives a 404', function () {
    Storage::disk('public')->put('ged_files/devis.pdf', '%PDF-1.4 test');
    $document = gedDocument('Devis', null, 'devis.pdf');

    $this->get(route('ged.documents.open', $document))->assertOk();
    $this->get(route('ged.documents.open', ['document' => $document, 'download' => 1]))->assertDownload('devis.pdf');

    expect($document->fresh()->downloads_count)->toBe(2);

    $this->get(route('ged.documents.open', gedDocument('Perdu', null, 'introuvable.pdf')))->assertNotFound();
});

test('vault files are stored privately and kept apart from the GED', function () {
    Storage::fake('local');
    gedDocument('Plaquette publique', null);

    $this->get(route('ged.vault'))->assertOk()->assertSee('Coffre-fort');

    Livewire::test('ged-explorer', ['vault' => true])
        ->assertDontSee('Plaquette publique')
        ->set('uploads', [UploadedFile::fake()->create('bilan_2025.pdf', 200)])
        ->assertHasNoErrors()
        ->assertSee('Bilan 2025');

    $secret = Document::where('file_name', 'bilan_2025.pdf')->firstOrFail();

    expect($secret->is_vault)->toBeTrue()
        ->and($secret->storageDisk())->toBe('local');
    Storage::disk('local')->assertExists($secret->file_path);
    Storage::disk('public')->assertMissing($secret->file_path);

    $this->get(route('ged.documents.open', $secret))->assertOk();

    Livewire::test('ged-explorer')->assertDontSee('Bilan 2025');
});

test('the vault mode cannot be switched off from the browser', function () {
    Livewire::test('ged-explorer', ['vault' => true])
        ->set('vault', false);
})->throws(CannotUpdateLockedPropertyException::class);
