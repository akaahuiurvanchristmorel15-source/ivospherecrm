<?php

use App\Models\User;

test('pages expose the pwa manifest and apple touch icon', function (string $uri) {
    $this->get($uri)
        ->assertOk()
        ->assertSee('<link rel="manifest" href="'.asset('manifest.webmanifest').'">', false)
        ->assertSee('<link rel="apple-touch-icon" href="'.asset('icons/apple-touch-icon.png').'">', false);
})->with(['/', '/login']);

test('authenticated layout exposes the pwa manifest', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('<link rel="manifest" href="'.asset('manifest.webmanifest').'">', false);
});

test('manifest is valid and its icons exist', function () {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest)
        ->display->toBe('standalone')
        ->start_url->toStartWith('/')
        ->and(collect($manifest['icons'])->pluck('sizes'))->toContain('192x192', '512x512')
        ->and(collect($manifest['icons'])->pluck('purpose'))->toContain('maskable');

    foreach ($manifest['icons'] as $icon) {
        expect(public_path(ltrim($icon['src'], '/')))->toBeFile();
    }

    expect(public_path('sw.js'))->toBeFile()
        ->and(public_path('offline.html'))->toBeFile();
});
