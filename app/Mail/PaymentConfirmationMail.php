<?php

namespace App\Mail;

use App\Models\PaymentRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/** Immediate payment confirmation after Moyasar marks the request paid. */
class PaymentConfirmationMail extends Mailable
{
    use SerializesModels;

    public string $subjectLine;

    public string $badge;

    public string $amountLabel;

    public string $amountCaption;

    public string $titleLabel;

    public string $headingEn;

    public string $bodyEn;

    public string $noteEn;

    public string $headingAr;

    public string $bodyAr;

    public string $noteAr;

    public ?string $actionUrl;

    public ?string $actionTextEn;

    public ?string $actionTextAr;

    public ?string $secondaryUrl = null;

    public ?string $secondaryTextEn = null;

    public ?string $secondaryTextAr = null;

    public ?string $secondaryCtaEn = null;

    public ?string $secondaryCtaAr = null;

    public function __construct(public PaymentRequest $paymentRequest)
    {
        $amount = $paymentRequest->amount().' '.$paymentRequest->currency;
        $titleEn = $paymentRequest->localizedTitle('en');
        $titleAr = $paymentRequest->localizedTitle('ar');
        $hasAccount = filled($paymentRequest->client_id);

        $this->subjectLine = Lang::get('portal.payments.email.confirm.subject', ['title' => $titleEn], 'en')
            .' | '
            .Lang::get('portal.payments.email.confirm.subject', ['title' => $titleAr], 'ar');
        $this->badge = Lang::get('portal.payments.email.confirm.badge', [], 'en');
        $this->amountLabel = $amount;
        $this->amountCaption = Lang::get('portal.payments.email.amount_caption', [], 'en')
            .' / '
            .Lang::get('portal.payments.email.amount_caption', [], 'ar');
        $this->titleLabel = $titleEn.($titleAr !== $titleEn ? ' · '.$titleAr : '');
        $this->headingEn = Lang::get('portal.payments.email.confirm.heading', [], 'en');
        $this->bodyEn = Lang::get('portal.payments.email.confirm.body', [
            'name' => $paymentRequest->name,
            'amount' => $amount,
            'title' => $titleEn,
        ], 'en');
        $noteKey = $hasAccount
            ? 'portal.payments.email.confirm.note'
            : 'portal.payments.email.confirm.note_guest';

        $this->noteEn = Lang::get($noteKey, [], 'en');
        $this->headingAr = Lang::get('portal.payments.email.confirm.heading', [], 'ar');
        $this->bodyAr = Lang::get('portal.payments.email.confirm.body', [
            'name' => $paymentRequest->name,
            'amount' => $amount,
            'title' => $titleAr,
        ], 'ar');
        $this->noteAr = Lang::get($noteKey, [], 'ar');

        if ($hasAccount) {
            $this->actionUrl = url('/login');
            $this->actionTextEn = Lang::get('portal.payments.email.confirm.action_login', [], 'en');
            $this->actionTextAr = Lang::get('portal.payments.email.confirm.action_login', [], 'ar');
        } else {
            $this->actionUrl = url('/register');
            $this->actionTextEn = Lang::get('portal.payments.email.confirm.action_register', [], 'en');
            $this->actionTextAr = Lang::get('portal.payments.email.confirm.action_register', [], 'ar');
            $this->secondaryUrl = url('/login');
            $this->secondaryTextEn = Lang::get('portal.payments.email.confirm.secondary', [], 'en');
            $this->secondaryTextAr = Lang::get('portal.payments.email.confirm.secondary', [], 'ar');
            $this->secondaryCtaEn = Lang::get('portal.payments.email.confirm.secondary_cta', [], 'en');
            $this->secondaryCtaAr = Lang::get('portal.payments.email.confirm.secondary_cta', [], 'ar');
        }
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
