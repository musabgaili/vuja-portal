<?php

namespace App\Services\Scope\Render;

use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;

/**
 * Headless-Chrome PDF engine (Browsershot/Puppeteer). It renders the SAME HTML
 * as the online preview, so Arabic (RTL) + the full-page letterhead come out
 * exactly as previewed — unlike mPDF, which mislays RTL text under the letterhead.
 *
 * Requires Node + Chromium on the server. Configure paths in config/scope.php
 * (node_binary / npm_binary / chrome_path) if they are not on PATH. Selected via
 * SCOPE_PDF_ENGINE=browsershot.
 */
class BrowsershotRenderer implements PdfRenderer
{
    public function download(string $html, string $filename): Response
    {
        return $this->respond($html, $filename, 'attachment');
    }

    public function stream(string $html, string $filename): Response
    {
        return $this->respond($html, $filename, 'inline');
    }

    private function respond(string $html, string $filename, string $disposition): Response
    {
        return response($this->build($html), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }

    private function build(string $html): string
    {
        $shot = Browsershot::html($html)
            ->showBackground()                 // print the letterhead background
            ->margins(0, 0, 0, 0)              // page geometry comes from the CSS @page
            ->format(strtolower((string) config('scope.page_size', 'letter')) === 'a4' ? 'A4' : 'Letter')
            ->waitUntilNetworkIdle();

        // Honour the document's own @page size/margins (the measured safe area).
        if (method_exists($shot, 'preferCssPageSize')) {
            $shot->preferCssPageSize();
        }

        // Server overrides (only set when the binaries are not on PATH).
        if ($node = config('scope.node_binary')) {
            $shot->setNodeBinary($node);
        }
        if ($npm = config('scope.npm_binary')) {
            $shot->setNpmBinary($npm);
        }
        if ($chrome = config('scope.chrome_path')) {
            $shot->setChromePath($chrome);
        }
        if (config('scope.browsershot_no_sandbox', true)) {
            $shot->noSandbox();
        }

        return $shot->pdf();
    }
}
