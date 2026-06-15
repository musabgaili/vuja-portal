<?php

namespace App\Services\Scope\Render;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pure-PHP PDF engine (mPDF) — ships without server ops and handles Arabic
 * shaping. mPDF doesn't honour position:fixed like Chrome, so the letterhead is
 * applied as a full-page watermark and the footer via SetHTMLFooter; content
 * flows inside the measured safe-area margins. Swap to Browsershot for exact
 * preview↔PDF parity by rebinding PdfRenderer in the service provider.
 */
class MpdfRenderer implements PdfRenderer
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

    /** Build the mPDF instance, write the HTML, return the PDF bytes. */
    private function build(string $html): string
    {
        $tmp = storage_path('app/mpdf');
        if (! is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }

        $safe = config('scope.safe_area_mm', ['top' => 40, 'side' => 18, 'bottom' => 24]);
        $format = strtolower((string) config('scope.page_size', 'letter')) === 'a4' ? 'A4' : 'Letter';

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'margin_top' => (float) ($safe['top'] ?? 40),
            'margin_left' => (float) ($safe['side'] ?? 18),
            'margin_right' => (float) ($safe['side'] ?? 18),
            'margin_bottom' => (float) ($safe['bottom'] ?? 24),
            'margin_header' => 0,
            'margin_footer' => 10,
            'tempDir' => $tmp,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->showImageErrors = false;

        // Full-page letterhead behind every page ('F' fills the page, 'P' centres it).
        $letterhead = storage_path('app/public/'.config('scope.letterhead', 'scope/letterhead.png'));
        if (is_file($letterhead)) {
            $mpdf->showWatermarkImage = true;
            $mpdf->watermarkImageAlpha = 1;
            $mpdf->SetWatermarkImage($letterhead, 1, 'F', 'P');
        }

        $footer = (string) config('scope.footer', '');
        if ($footer !== '') {
            $mpdf->SetHTMLFooter('<div style="text-align:center;font-size:8.5px;color:#5a6a6c;">'.e($footer).'</div>');
        }

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
