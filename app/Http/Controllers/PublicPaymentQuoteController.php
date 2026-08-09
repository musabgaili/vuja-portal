<?php

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use App\Services\Scope\Render\PdfRenderer;
use App\Services\Scope\Render\QuoteDocumentRenderer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PublicPaymentQuoteController extends Controller
{
    public function __invoke(
        PaymentRequest $paymentRequest,
        QuoteDocumentRenderer $documents,
        PdfRenderer $renderer,
    ): Response {
        abort_if($paymentRequest->isExpired() && $paymentRequest->status !== 'paid', 410, __('portal.payments.expired'));
        abort_if(in_array($paymentRequest->status, ['cancelled', 'refunded', 'voided'], true), 410);

        if ($paymentRequest->hasQuoteFile() && Storage::disk('private')->exists($paymentRequest->quote_file)) {
            return Storage::disk('private')->download($paymentRequest->quote_file, $paymentRequest->quoteFileName());
        }

        $quote = $paymentRequest->quote();
        abort_unless($quote, 404, __('portal.payments.quote_missing'));

        try {
            return $renderer->download(
                $documents->html($quote, $documents->engineFlags()),
                $documents->filename($quote),
            );
        } catch (\Throwable $e) {
            Log::error('Public payment quote export failed', [
                'payment_request_uuid' => $paymentRequest->uuid,
                'quote_id' => $quote->id,
                'error' => $e->getMessage(),
            ]);

            abort(503, __('portal.payments.quote_download_failed'));
        }
    }
}
