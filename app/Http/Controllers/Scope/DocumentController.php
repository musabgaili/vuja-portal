<?php

namespace App\Http\Controllers\Scope;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\Scope\Render\DocxRenderer;
use App\Services\Scope\Render\PdfRenderer;
use App\Services\Scope\Render\QuoteDocumentRenderer;
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
    public function __construct(private QuoteDocumentRenderer $documents) {}

    /** Full-page online document viewer (also the PDF source). */
    public function document(Quote $quote)
    {
        $this->guard($quote);

        return response($this->documents->html($quote));
    }

    /** PDF download. */
    public function pdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);

        return $this->safeExport($quote, fn () => $renderer->download(
            $this->documents->html($quote, $this->documents->engineFlags()),
            $this->documents->filename($quote),
        ));
    }

    /** PDF streamed inline (view in the browser before downloading). */
    public function viewPdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);

        return $this->safeExport($quote, fn () => $renderer->stream(
            $this->documents->html($quote, $this->documents->engineFlags()),
            $this->documents->filename($quote),
        ));
    }

    /** DOCX download (branded equivalent). */
    public function docx(Quote $quote, DocxRenderer $renderer)
    {
        $this->guard($quote);

        return $this->safeExport($quote, fn () => $renderer->download($quote, $this->documents->filename($quote, 'docx')));
    }

    /** Company technical offer (technical sections only, no commercial tables). */
    public function technicalPdf(Quote $quote, PdfRenderer $renderer)
    {
        $this->guard($quote);
        abort_unless($quote->customer_category === 'company', 404);

        return $this->safeExport($quote, fn () => $renderer->download(
            $this->documents->html($quote, ['technical' => true] + $this->documents->engineFlags()),
            $this->documents->filename($quote, 'pdf', '-technical'),
        ));
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

    private function guard(Quote $quote): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal(), 403);
        abort_unless($user->isManager() || $user->isProjectManager() || $quote->created_by === $user->id, 403);
    }
}
