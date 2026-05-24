<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_welcome_page_includes_pwa_metadata(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('name="theme-color"', false)
            ->assertSee('apple-mobile-web-app-capable', false);
    }

    public function test_offline_page_is_public_and_branded(): void
    {
        $response = $this->get('/offline');

        $response->assertOk()
            ->assertSee('Harviana - Offline', false)
            ->assertSee('You are offline')
            ->assertSee('manifest.webmanifest', false);
    }

    public function test_manifest_has_required_pwa_fields_and_existing_icons(): void
    {
        $path = public_path('manifest.webmanifest');

        $this->assertFileExists($path);

        $manifest = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('Harviana Agricultural Decision Support System', $manifest['name']);
        $this->assertSame('Harviana', $manifest['short_name']);
        $this->assertSame('/dashboard', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#4d7c0f', $manifest['theme_color']);

        $icons = collect($manifest['icons']);
        $this->assertTrue($icons->contains(fn (array $icon): bool => $icon['sizes'] === '192x192' && str_contains($icon['purpose'], 'any')));
        $this->assertTrue($icons->contains(fn (array $icon): bool => $icon['sizes'] === '512x512' && str_contains($icon['purpose'], 'maskable')));

        foreach ($icons as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_service_worker_contains_release_cache_and_offline_route(): void
    {
        $path = public_path('sw.js');

        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        $this->assertStringContainsString("CACHE_VERSION = 'v1.15.17'", $contents);
        $this->assertStringContainsString("const OFFLINE_URL = '/offline'", $contents);
        $this->assertStringContainsString('CLEAR_RUNTIME_CACHES', $contents);
        $this->assertStringContainsString('handleNavigationRequest', $contents);
    }
}
