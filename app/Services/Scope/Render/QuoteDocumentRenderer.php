<?php

namespace App\Services\Scope\Render;

use App\Models\Quote;

/**
 * Single HTML source for quotation preview and PDF export.
 * Used by the internal Scope Planner and the public payment download.
 */
class QuoteDocumentRenderer
{
    public function html(Quote $quote, array $extra = []): string
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

    public function engineFlags(): array
    {
        return config('scope.pdf_engine', 'mpdf') === 'browsershot'
            ? ['embedAssets' => true]
            : ['pdf' => true];
    }

    public function filename(Quote $quote, string $ext = 'pdf', string $suffix = ''): string
    {
        return ($quote->quote_number ?: 'quote-'.$quote->id).$suffix.'.'.$ext;
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
}
