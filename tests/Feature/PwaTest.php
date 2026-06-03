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
        $this->assertSame('/app', $manifest['id']);
        $this->assertSame('/app', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#355872', $manifest['theme_color']);
        $this->assertSame('en-PH', $manifest['lang']);
        $this->assertFalse($manifest['prefer_related_applications']);

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

        $this->assertStringContainsString("CACHE_VERSION = 'v1.15.21'", $contents);
        $this->assertStringContainsString("const OFFLINE_URL = '/offline'", $contents);
        $this->assertStringContainsString("'/app'", $contents);
        $this->assertStringContainsString('CLEAR_RUNTIME_CACHES', $contents);
        $this->assertStringContainsString('handleNavigationRequest', $contents);
    }

    public function test_pwa_launch_route_redirects_by_session_state(): void
    {
        $this->get('/app')
            ->assertRedirect(route('welcome', absolute: false));
    }

    public function test_mobile_viewport_and_safe_area_assets_are_configured(): void
    {
        $this->assertStringContainsString(
            'apple-mobile-web-app-status-bar-style" content="black-translucent"',
            file_get_contents(resource_path('views/partials/pwa.blade.php'))
        );
        $this->assertStringContainsString(
            'name="theme-color" content="#355872"',
            file_get_contents(resource_path('views/partials/pwa.blade.php'))
        );
        $this->assertStringContainsString(
            'format-detection" content="telephone=no"',
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
