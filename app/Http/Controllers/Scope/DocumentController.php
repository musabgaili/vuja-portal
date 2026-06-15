<?php

namespace App\Http\Controllers\Scope;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\Scope\Render\DocxRenderer;
use App\Services\Scope\Render\PdfRenderer;
use Illuminate\Support\Facades\Auth;

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

        return $renderer->download($this->renderHtml($quote, ['pdf' => true]), $this->filename($quote));
    }

    /** PDF streamed inline (view in the browser before downloading). */
    public function viewPdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);

        return $renderer->stream($this->renderHtml($quote, ['pdf' => true]), $this->filename($quote));
    }

    /** DOCX download (branded equivalent). */
    public function docx(Quote $quote, DocxRenderer $renderer)
    {
        $this->guard($quote);

        return $renderer->download($quote, $this->filename($quote, 'docx'));
    }

    /** Company technical offer (technical sections only, no commercial tables). */
    public function technicalPdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);
        abort_unless($quote->customer_category === 'company', 404);

        return $renderer->download($this->renderHtml($quote, ['technical' => true, 'pdf' => true]), $this->filename($quote, 'pdf', '-technical'));
    }

    /** Render the tier document to HTML in the quote's OWN language (not the UI locale). */
    private function renderHtml(Quote $quote, array $extra = []): string
    {
        $previous = app()->getLocale();
        app()->setLocale($quote->language ?: $previous);

        try {
            return view($this->tierView($quote), $this->viewData($quote) + $extra)->render();
        } finally {
            app()->setLocale($previous);
        }
    }

    private function viewData(Quote $quote): array
    {
        $quote->loadMissing(['items', 'scopes', 'milestones', 'client', 'creator']);

        return ['quote' => $quote, 'audience' => 'client', 'technical' => false];
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
