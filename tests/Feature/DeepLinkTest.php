<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeepLinkTest extends TestCase
{
    public function test_android_asset_links_are_public_json(): void
    {
        config([
            'mobile.android.package' => 'com.vujade.portal',
            'mobile.android.sha256_cert_fingerprints' => ['AB:CD:EF'],
        ]);

        $this->get('/.well-known/assetlinks.json')
            ->assertOk()
            ->assertJsonPath('0.target.package_name', 'com.vujade.portal')
            ->assertJsonPath('0.target.sha256_cert_fingerprints.0', 'AB:CD:EF');
    }

    public function test_apple_app_site_association_is_public_json(): void
    {
        config([
            'mobile.ios.team_id' => 'TEAMID',
            'mobile.ios.bundle_id' => 'com.vujade.portal',
        ]);

        $this->get('/.well-known/apple-app-site-association')
            ->assertOk()
            ->assertJsonPath('applinks.details.0.appID', 'TEAMID.com.vujade.portal');
    }

    public function test_app_path_renders_custom_scheme_fallback(): void
    {
        config(['mobile.scheme' => 'vujade']);

        $this->get('/app/projects/42')
            ->assertOk()
            ->assertSee('vujade://app/projects/42', false);
    }
}
