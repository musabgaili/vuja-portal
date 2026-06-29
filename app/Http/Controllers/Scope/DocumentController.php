<?php

namespace App\Http\Controllers\Scope;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\Scope\Render\DocxRenderer;
use App\Services\Scope\Render\PdfRenderer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SfResponse;

/**
 * Renders the scope/quotation document for the online viewer (the "see before
 * download" surface) and exports it to PDF / DOCX. The Blade view is the single
 * source for both the preview and the PDF.
 */
class DocumentController extends Controller
{
    /** Full-page online document viewer (also the PDF source). */
    public function document(Quote $quote)
    {
        $this->guard($quote);

        return response($this->renderHtml($quote));
    }

    /** PDF download. */
    public function pdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);

        return $this->safeExport($quote, fn () => $renderer->download($this->renderHtml($quote, $this->engineFlags()), $this->filename($quote)));
    }

    /** PDF streamed inline (view in the browser before downloading). */
    public function viewPdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);

        return $this->safeExport($quote, fn () => $renderer->stream($this->renderHtml($quote, $this->engineFlags()), $this->filename($quote)));
    }

    /** DOCX download (branded equivalent). */
    public function docx(Quote $quote, DocxRenderer $renderer)
    {
        $this->guard($quote);

        return $this->safeExport($quote, fn () => $renderer->download($quote, $this->filename($quote, 'docx')));
    }

    /** Company technical offer (technical sections only, no commercial tables). */
    public function technicalPdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);
        abort_unless($quote->customer_category === 'company', 404);

        return $this->safeExport($quote, fn () => $renderer->download($this->renderHtml($quote, ['technical' => true] + $this->engineFlags()), $this->filename($quote, 'pdf', '-technical')));
    }

    /**
     * HTML-mode flags per PDF engine. mPDF needs the pdf-mode HTML (it supplies
     * the letterhead watermark + margins). Browsershot is Chrome, so it renders
     * the SAME HTML as the online preview (pdf=false) — letterhead + RTL come out
     * exactly as previewed.
     */
    private function engineFlags(): array
    {
        // Browsershot is Chrome → render the SAME HTML as the preview (pdf=false),
        // but embed the letterhead as a data URI so it never depends on Chrome
        // fetching the asset URL over the network. mPDF supplies its own letterhead.
        return config('scope.pdf_engine', 'mpdf') === 'browsershot'
            ? ['embedAssets' => true]
            : ['pdf' => true];
    }

    /**
     * Run an export closure, turning any failure (e.g. the PDF/DOCX library not
     * installed on the server, or a render error) into a logged, friendly redirect
     * back to the editor instead of a raw 500. The online viewer always works, so
     * the user is never fully blocked.
     */
    private function safeExport(Quote $quote, \Closure $fn): SfResponse
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::error('Scope document export failed', [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'error' => $e->getMessage(),
                'at' => $e->getFile().':'.$e->getLine(),
            ]);

            return redirect()->route('scope-planner.show', $quote)
                ->with('error', __('portal.scope_planner.export_failed'));
        }
    }

    /** Render the tier document to HTML in the quote's OWN language (not the UI locale). */
    private function renderHtml(Quote $quote, array $extra = []): string
    {
        $previous = app()->getLocale();
        app()->setLocale($quote->language ?: $previous);

        try {
            // $extra MUST win over the defaults: the array `+` operator keeps the
            // LEFT operand on key collisions, so caller flags (e.g. technicalPdf's
            // 'technical' => true) have to be the left side or they are silently
            // discarded — which previously made the technical offer leak pricing.
            return view($this->tierView($quote), $extra + $this->viewData($quote))->render();
        } finally {
            app()->setLocale($previous);
        }
    }

    private function viewData(Quote $quote): array
    {
        $quote->loadMissing(['items', 'scopes', 'milestones', 'client', 'creator']);

        return ['quote' => $quote, 'technical' => false];
    }

    private function tierView(Quote $quote): string
    {
        return 'scope.'.match ($quote->customer_category) {
            'student' => 'student',
            'entrepreneur' => 'entrepreneur',
            default => 'company',
        };
    }

    private function filename(Quote $quote, string $ext = 'pdf', string $suffix = ''): string
    {
        return ($quote->quote_number ?: 'quote-'.$quote->id).$suffix.'.'.$ext;
    }

    private function guard(Quote $quote): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal(), 403);
        abort_unless($user->isManager() || $user->isProjectManager() || $quote->created_by === $user->id, 403);
    }
}
