<?php

namespace App\Mail;

use App\Models\PaymentRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/** Immediate (non-queued) payment-link email so Resend delivers without a queue worker. */
class PaymentRequestMail extends Mailable
{
    use SerializesModels;

    public string $subjectLine;

    public string $badge;

    public string $amountLabel;

    public string $amountCaption;

    public string $titleLabel;

    public string $headingEn;

    public string $bodyEn;

    public string $headingAr;

    public string $bodyAr;

    public string $actionUrl;

    public string $actionTextEn;

    public string $actionTextAr;

    public function __construct(
        public PaymentRequest $paymentRequest,
        string $actionUrl,
    ) {
        $amount = $paymentRequest->amount().' '.$paymentRequest->currency;
        $expires = $paymentRequest->expires_at->timezone('UTC')->format('M j, Y g:i A').' UTC';
        $titleEn = $paymentRequest->localizedTitle('en');
        $titleAr = $paymentRequest->localizedTitle('ar');

        $this->subjectLine = Lang::get('portal.payments.email.link.subject', ['title' => $titleEn], 'en')
            .' | '
            .Lang::get('portal.payments.email.link.subject', ['title' => $titleAr], 'ar');
        $this->badge = Lang::get('portal.payments.email.link.badge', [], 'en');
        $this->amountLabel = $amount;
        $this->amountCaption = Lang::get('portal.payments.email.amount_caption', [], 'en')
            .' / '
            .Lang::get('portal.payments.email.amount_caption', [], 'ar');
        $this->titleLabel = $titleEn.($titleAr !== $titleEn ? ' · '.$titleAr : '');
        $this->headingEn = Lang::get('portal.payments.email.link.heading', [], 'en');
        $this->bodyEn = Lang::get('portal.payments.email.link.body', [
            'name' => $paymentRequest->name,
            'amount' => $amount,
            'expires' => $expires,
            'title' => $titleEn,
        ], 'en');
        $this->headingAr = Lang::get('portal.payments.email.link.heading', [], 'ar');
        $this->bodyAr = Lang::get('portal.payments.email.link.body', [
            'name' => $paymentRequest->name,
            'amount' => $amount,
            'expires' => $expires,
            'title' => $titleAr,
        ], 'ar');
        $this->actionUrl = $actionUrl;
        $this->actionTextEn = Lang::get('portal.payments.email.link.action', [], 'en');
        $this->actionTextAr = Lang::get('portal.payments.email.link.action', [], 'ar');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment');
    }
}
