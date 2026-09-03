<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Universal Links / App Links + custom-scheme fallback so shared team/client
 * URLs open the Flutter app on the correct screen.
 */
class DeepLinkController extends Controller
{
    /** Android Digital Asset Links. */
    public function assetLinks(): JsonResponse
    {
        $package = (string) config('mobile.android.package');
        $fps = config('mobile.android.sha256_cert_fingerprints', []);

        $target = [
            'namespace' => 'android_app',
            'package_name' => $package,
            'sha256_cert_fingerprints' => $fps ?: ['REPLACE_WITH_RELEASE_SHA256'],
        ];

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => $target,
        ]]);
    }

    /** Apple App Site Association (no file extension; JSON content-type). */
    public function appleAppSiteAssociation(): Response
    {
        $appId = $this->iosAppId();
        $paths = config('mobile.ios.paths', ['/app/*']);

        $body = [
            'applinks' => [
                'apps' => [],
                'details' => [
                    [
                        'appID' => $appId,
                        'paths' => $paths,
                    ],
                ],
            ],
            'webcredentials' => [
                'apps' => $appId ? [$appId] : [],
            ],
        ];

        return response()->json($body, 200, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Shared URL: https://host/app/projects/12 → opens vujade://app/projects/12.
     * Browsers that cannot open the app see a simple fallback page.
     */
    public function open(Request $request, string $path): View
    {
        $scheme = config('mobile.scheme', 'vujade');
        $deepLink = $scheme.'://app/'.$path;
        $query = $request->getQueryString();
        if ($query) {
            $deepLink .= '?'.$query;
        }

        return view('deeplink.open', [
            'deepLink' => $deepLink,
            'path' => $path,
        ]);
    }

    private function iosAppId(): string
    {
        $configured = (string) config('mobile.ios.app_id', '');
        if ($configured !== '') {
            return $configured;
        }

        $team = (string) config('mobile.ios.team_id', '');
        $bundle = (string) config('mobile.ios.bundle_id', 'com.vujade.portal');

        return $team !== '' ? $team.'.'.$bundle : $bundle;
    }
}
