<?php

namespace App\Services\Scope\Render;

use Symfony\Component\HttpFoundation\Response;

/** Swappable PDF engine for scope documents (mPDF now; Browsershot later). */
interface PdfRenderer
{
    public function download(string $html, string $filename): Response;

    /** Stream inline so the browser shows the PDF before the user downloads it. */
    public function stream(string $html, string $filename): Response;
}
