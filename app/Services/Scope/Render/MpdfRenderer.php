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

        $format = strtolower((string) config('scope.page_size', 'letter')) === 'a4' ? 'A4' : 'Letter';
        $brand = (string) config('scope.brand_color', '#1B565E');

        // NOTE: we deliberately do NOT use a full-page letterhead watermark here —
        // mPDF's SetWatermarkImage with this letterhead breaks/clips the body text
        // layout (especially RTL). Instead we render a clean branded header band +
        // footer (mPDF's intended mechanism); the body content stays intact. The
        // exact full-bleed letterhead is the Browsershot engine's job
        // (SCOPE_PDF_ENGINE=browsershot), which renders the same HTML as the preview.
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'margin_top' => 32,
            'margin_left' => 16,
            'margin_right' => 16,
            'margin_bottom' => 22,
            'margin_header' => 8,
            'margin_footer' => 9,
            'tempDir' => $tmp,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->showImageErrors = false;

        // Branded header band (teal + white logo) repeated on every page.
        $logo = public_path('images/vd-logo-dark-trimmed.png');
        if (! is_file($logo)) {
            $logo = public_path('images/vd-logo-dark.png');
        }
        $logoTag = is_file($logo) ? '<img src="'.$logo.'" style="height:30px;">' : '<span style="color:#fff;font-weight:700;font-size:16px;">'.e(config('scope.company_name', 'Vuja De Innovation')).'</span>';
        $mpdf->SetHTMLHeader(
            '<table width="100%" style="border:0;"><tr>'
            .'<td style="background:'.$brand.';padding:7px 14px;border-radius:0 0 12px 0;width:42%;">'.$logoTag.'</td>'
            .'<td style="border:0;"></td></tr></table>'
        );

        $footer = (string) config('scope.footer', '');
        if ($footer !== '') {
            $mpdf->SetHTMLFooter('<div style="border-top:1px solid '.$brand.';padding-top:4px;text-align:center;font-size:8px;color:#5a6a6c;">'.e($footer).'</div>');
        }

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
