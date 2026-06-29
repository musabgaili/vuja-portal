<?php

namespace App\Services\Scope\Render;

use App\Models\Quote;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Writer\Word2007;
use Symfony\Component\HttpFoundation\Response;

/**
 * Branded DOCX equivalent of the scope document (PHPWord). Mirrors the BoQ /
 * totals / milestones tables; the letterhead sits behind the text in the page
 * header so it repeats on every page. RTL-aware for Arabic.
 */
class DocxRenderer
{
    public function download(Quote $quote, string $filename): Response
    {
        $previous = app()->getLocale();
        app()->setLocale($quote->language ?: $previous);

        try {
            $bytes = $this->build($quote);
        } finally {
            app()->setLocale($previous);
        }

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function build(Quote $quote): string
    {
        $quote->loadMissing(['items', 'scopes', 'milestones', 'client']);
        $rtl = $quote->language === 'ar';
        // Label helper: honour the employee's per-quote heading/column renames
        // (doc_labels) so the DOCX matches the PDF/online document.
        $L = fn (string $k, array $r = []) => $quote->label($k, __($k, $r));
        $cur = config('scope.currency', 'SAR');
        $brand = ltrim((string) config('scope.brand_color', '#1B565E'), '#');

        $word = new PhpWord();
        $word->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language($rtl ? 'ar-SA' : 'en-US'));
        $word->setDefaultFontName('DejaVu Sans');
        $word->setDefaultFontSize(11);

        $para = ['alignment' => $rtl ? 'right' : 'left', 'bidi' => $rtl, 'spaceAfter' => 120];
        $run = ['rtl' => $rtl];
        $h2 = ['bold' => true, 'size' => 13, 'color' => $brand, 'rtl' => $rtl];

        $section = $word->addSection([
            'marginTop' => $this->mm($quote, 'top', 40), 'marginBottom' => $this->mm($quote, 'bottom', 24),
            'marginLeft' => $this->mm($quote, 'side', 18), 'marginRight' => $this->mm($quote, 'side', 18),
        ]);

        // Letterhead behind text (repeats every page via the header).
        // Path is relative to public/ (git-tracked) so it ships with the repo.
        $letterhead = public_path(config('scope.letterhead', 'images/scope-letterhead.png'));
        if (is_file($letterhead)) {
            // Full-page behind-text letterhead. PHPWord image dimensions are in
            // POINTS, so a full page = page size in points (Letter 612x792, A4 595x842).
            [$lhW, $lhH] = strtolower((string) config('scope.page_size', 'letter')) === 'a4' ? [595, 842] : [612, 792];
            $section->addHeader()->addImage($letterhead, [
                'width' => $lhW, 'height' => $lhH, 'wrappingStyle' => 'behind',
                'posHorizontal' => 'center', 'posHorizontalRelTo' => 'page',
                'posVertical' => 'top', 'posVerticalRelTo' => 'page',
            ]);
        }

        // Footer
        $footer = $section->addFooter();
        $footer->addText(e((string) config('scope.footer', '')), ['size' => 8, 'color' => '5a6a6c'], ['alignment' => 'center']);

        // Title + meta
        $section->addText(__('scope.scope_of_work'), ['bold' => true, 'size' => 18, 'color' => $brand, 'rtl' => $rtl], ['alignment' => 'center', 'spaceAfter' => 200]);
        $this->metaTable($section, $quote, $rtl);

        $c = $quote->ai_content ?? [];

        // Narrative sections (strings → paragraph, arrays → bullets).
        foreach ($this->narrativeKeys($quote) as $key) {
            if (empty($c[$key])) {
                continue;
            }
            $section->addText($L('scope.'.$key), $h2, ['spaceBefore' => 200, 'spaceAfter' => 80]);
            if (is_array($c[$key])) {
                foreach ($c[$key] as $line) {
                    $section->addListItem((string) $line, 0, $run, null, $para);
                }
            } else {
                $section->addText((string) $c[$key], $run, $para);
            }
        }

        // Company per-scope detail
        foreach ($quote->scopes as $i => $s) {
            $section->addText(__('scope.scope').' '.($i + 1).' — '.$s->title, $h2, ['spaceBefore' => 200]);
            foreach (['objective', 'inputs_required', 'deliverables', 'acceptance_criteria', 'exclusions'] as $f) {
                $val = $s->{$f};
                if (empty($val)) {
                    continue;
                }
                $section->addText($L('scope.'.$f), ['bold' => true, 'rtl' => $rtl], ['spaceBefore' => 80]);
                foreach ((array) $val as $line) {
                    $section->addListItem((string) $line, 0, $run, null, $para);
                }
            }
        }

        // Pricing (BoQ)
        $section->addText($L('scope.pricing'), $h2, ['spaceBefore' => 240, 'spaceAfter' => 80]);
        $this->boqTable($section, $quote, $cur, $brand);

        // Milestones
        if ($quote->milestones->isNotEmpty()) {
            $section->addText($L('scope.payment_schedule'), $h2, ['spaceBefore' => 200, 'spaceAfter' => 80]);
            $this->milestoneTable($section, $quote, $cur, $brand);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'scopedocx');
        (new Word2007($word))->save($tmp);
        $bytes = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $bytes;
    }

    private function metaTable($section, Quote $quote, bool $rtl): void
    {
        $t = $section->addTable(['borderSize' => 6, 'borderColor' => 'd3dedd', 'cellMargin' => 60, 'width' => 100 * 50, 'unit' => 'pct']);
        foreach ([
            'to' => $quote->client_name, 'email' => $quote->client_email,
            'ref' => $quote->quote_number, 'subject' => $quote->subject,
        ] as $k => $v) {
            $t->addRow();
            $t->addCell(2600, ['bgColor' => 'eaf2f2'])->addText(__('scope.'.$k), ['bold' => true, 'rtl' => $rtl]);
            $t->addCell(7000)->addText((string) $v, ['rtl' => $rtl]);
        }
    }

    private function boqTable($section, Quote $quote, string $cur, string $brand): void
    {
        $t = $section->addTable(['borderSize' => 6, 'borderColor' => 'd3dedd', 'cellMargin' => 60, 'width' => 100 * 50, 'unit' => 'pct']);
        $t->addRow();
        $L = fn (string $k) => $quote->label($k, __($k));
        foreach ([$L('scope.item_no'), $L('scope.description'), $L('scope.qty'), $L('scope.unit_price').' ('.$cur.')', $L('scope.total').' ('.$cur.')'] as $head) {
            $t->addCell(null, ['bgColor' => $brand])->addText($head, ['bold' => true, 'color' => 'FFFFFF']);
        }
        foreach ($quote->clientVisibleItems() as $i => $row) {
            $t->addRow();
            $t->addCell(700)->addText((string) ($i + 1));
            $t->addCell(5200)->addText((string) $row->description);
            $t->addCell(1000)->addText(rtrim(rtrim(number_format($row->quantity, 2), '0'), '.'));
            $t->addCell(1700)->addText(number_format($row->unit_price, 0));
            $t->addCell(1700)->addText(number_format($row->line_total, 0));
        }
        foreach ([
            ['scope.subtotal', $quote->subtotal],
            ['scope.vat', $quote->vat_amount, ['rate' => rtrim(rtrim(number_format($quote->vat_rate, 2), '0'), '.')]],
            ['scope.grand_total', $quote->grand_total],
        ] as $r) {
            $t->addRow();
            $cell = $t->addCell(8600, ['gridSpan' => 4, 'bgColor' => 'eaf2f2']);
            $cell->addText($quote->label($r[0], __($r[0], $r[2] ?? [])), ['bold' => true, 'color' => $brand]);
            $t->addCell(1700, ['bgColor' => 'eaf2f2'])->addText(number_format($r[1], 0), ['bold' => true]);
        }
    }

    private function milestoneTable($section, Quote $quote, string $cur, string $brand): void
    {
        $t = $section->addTable(['borderSize' => 6, 'borderColor' => 'd3dedd', 'cellMargin' => 60, 'width' => 100 * 50, 'unit' => 'pct']);
        $t->addRow();
        $L = fn (string $k) => $quote->label($k, __($k));
        foreach ([$L('scope.milestone'), $L('scope.trigger'), '%', $L('scope.amount').' ('.$cur.')'] as $head) {
            $t->addCell(null, ['bgColor' => $brand])->addText($head, ['bold' => true, 'color' => 'FFFFFF']);
        }
        foreach ($quote->milestones as $m) {
            $t->addRow();
            $t->addCell(1500)->addText($m->code);
            $t->addCell(6000)->addText($m->triggerLabel());
            $t->addCell(1200)->addText(rtrim(rtrim(number_format($m->percentage, 2), '0'), '.').'%');
            $t->addCell(2000)->addText(number_format($m->amount, 0));
        }
    }

    private function narrativeKeys(Quote $quote): array
    {
        return match ($quote->customer_category) {
            'student' => ['needs', 'scope_of_work', 'out_of_scope', 'notes'],
            'entrepreneur' => ['introduction', 'proposed_scope', 'technical_specs', 'mechanical_specs', 'operational_logic', 'implementation_phases', 'out_of_scope', 'notes'],
            default => ['introduction_purpose'],
        };
    }

    /** mm → twips (1 mm = 56.6929 twips). */
    private function mm(Quote $quote, string $key, float $default): int
    {
        $safe = config('scope.safe_area_mm', []);

        return (int) round(((float) ($safe[$key] ?? $default)) * 56.6929);
    }
}
