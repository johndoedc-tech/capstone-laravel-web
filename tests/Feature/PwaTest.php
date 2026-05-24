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
        $this->assertSame('fullscreen', $manifest['display']);
        $this->assertSame(['fullscreen', 'standalone'], $manifest['display_override']);
        $this->assertSame('#f7f8f0', $manifest['theme_color']);

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

    public function test_mobile_viewport_and_safe_area_assets_are_configured(): void
    {
        $this->assertStringContainsString(
            'apple-mobile-web-app-status-bar-style" content="black-translucent"',
            file_get_contents(resource_path('views/partials/pwa.blade.php'))
        );
        $this->assertStringContainsString(
            'name="theme-color" content="#f7f8f0"',
            file_get_contents(resource_path('views/partials/pwa.blade.php'))
        );

        foreach ([
            resource_path('views/layouts/app.blade.php'),
            resource_path('views/layouts/admin.blade.php'),
            resource_path('views/layouts/guest.blade.php'),
            resource_path('views/auth/onboarding.blade.php'),
            resource_path('views/auth/password-change-required.blade.php'),
            resource_path('views/welcome.blade.php'),
            resource_path('views/offline.blade.php'),
        ] as $viewPath) {
            $this->assertStringContainsString('viewport-fit=cover', file_get_contents($viewPath));
        }

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('--harviana-viewport-height: 100dvh', $css);
        $this->assertStringContainsString('env(safe-area-inset-top', $css);
        $this->assertStringContainsString('.mobile-sidebar', $css);

        $js = file_get_contents(resource_path('js/pwa.js'));
        $this->assertStringContainsString('syncViewportHeight', $js);
        $this->assertStringContainsString('syncThemeColor', $js);
        $this->assertStringContainsString('window.visualViewport', $js);
    }
}
