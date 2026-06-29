{{-- Company tier — branded SoW + commercial proposal + terms. Multi-scope aware.
     When $technical is true this renders the technical offer (no commercial tables). --}}
@extends('scope.document')
@php($c = $quote->ai_content ?? [])
@php($cur = $quote->language === 'ar' ? 'ر.س' : 'SAR')
@php($technical = $technical ?? false)
{{-- Label helper: employee's per-quote override (doc_labels) → translation fallback. --}}
@php($L = fn ($k) => $quote->label($k, __($k)))

@section('body')
  {{-- ===== Cover ===== --}}
  <div class="cover-title">@lang('scope.scope_of_work')</div>
  <div class="cover-sub">@lang('scope.company_tagline')</div>
  <table class="meta cover-meta">
    <tr><td class="k">@lang('scope.prepared_by')</td><td>{{ config('scope.company_name', 'Vuja De Innovation') }}</td></tr>
    <tr><td class="k">@lang('scope.engagement')</td><td>{{ $quote->subject }}</td></tr>
    <tr><td class="k">@lang('scope.beneficiary')</td><td>{{ $quote->beneficiary ?? $quote->client_name }}</td></tr>
    <tr><td class="k">@lang('scope.validity')</td><td>@lang('scope.validity_value', ['days' => $quote->validity_days])</td></tr>
  </table>

  {{-- ===== Introduction + Pricing Structure ===== --}}
  <div class="pagebreak"></div>
  @if(!empty($c['introduction_purpose']))
    <div class="section"><h2>{{ $L('scope.introduction_purpose') }}</h2><p class="para">{{ $c['introduction_purpose'] }}</p></div>
  @endif

  @unless($technical)
    @if($quote->scopes->isNotEmpty())
      <div class="section"><h2>{{ $L('scope.pricing_structure') }}</h2>
        <table class="tbl"><thead><tr>
          <th style="width:6%">#</th><th class="desc">{{ $L('scope.scope') }}</th><th>{{ $L('scope.type') }}</th><th>{{ $L('scope.qty') }}</th><th>{{ $L('scope.value') }} ({{ $cur }})</th>
        </tr></thead><tbody>
          @foreach($quote->scopes as $s)
            <tr><td class="c">{{ $loop->iteration }}</td><td class="desc">{{ $s->title }}</td>
              <td class="c">{{ $s->type === 'sponsored' ? __('scope.sponsored') : __('scope.commercial') }}</td>
              <td class="c">1</td><td class="money">{{ number_format($s->price,0) }}</td></tr>
          @endforeach
          <tr class="tot"><td class="totlabel" colspan="4">{{ $L('scope.subtotal') }}</td><td class="money">{{ number_format($quote->subtotal,0) }}</td></tr>
        </tbody></table>
      </div>
    @endif
  @endunless

  {{-- ===== Per-scope detail ===== --}}
  @foreach($quote->scopes as $s)
    <div class="section">
      <h2>{{ $L('scope.scope') }} {{ $loop->iteration }} — {{ $s->title }}</h2>
      @if(!empty($s->objective))<h3>{{ $L('scope.objective') }}</h3><p class="para">{{ is_array($s->objective) ? implode(' ', $s->objective) : $s->objective }}</p>@endif
      @if(!empty($s->inputs_required))<h3>{{ $L('scope.inputs_required') }}</h3><ul class="points">@foreach($s->inputs_required as $x)<li>{{ $x }}</li>@endforeach</ul>@endif
      @if(!empty($s->deliverables))<h3>{{ $L('scope.deliverables') }}</h3><ul class="points">@foreach($s->deliverables as $x)<li>{{ $x }}</li>@endforeach</ul>@endif
      @if(!empty($s->acceptance_criteria))<h3>{{ $L('scope.acceptance_criteria') }}</h3><ul class="points">@foreach($s->acceptance_criteria as $x)<li>{{ $x }}</li>@endforeach</ul>@endif
      @if(!empty($s->exclusions))<h3>{{ $L('scope.exclusions') }}</h3><ul class="points">@foreach($s->exclusions as $x)<li>{{ $x }}</li>@endforeach</ul>@endif
    </div>
  @endforeach

  {{-- ===== Commercial proposal (omitted on the technical offer) ===== --}}
  @unless($technical)
    <div class="section">
      <h2>{{ $L('scope.commercial_proposal') }}</h2>

      <h3>{{ $L('scope.price_summary') }}</h3>
      <table class="tbl"><tbody>
        <tr class="sub"><td class="totlabel">{{ $L('scope.subtotal') }}</td><td class="money" style="width:24%">{{ number_format($quote->subtotal,0) }}</td></tr>
        <tr class="vat"><td class="totlabel">@lang('scope.vat', ['rate' => rtrim(rtrim(number_format($quote->vat_rate,2),'0'),'.')])</td><td class="money">{{ number_format($quote->vat_amount,0) }}</td></tr>
        <tr class="tot"><td class="totlabel">{{ $L('scope.grand_total') }}</td><td class="money">{{ number_format($quote->grand_total,0) }}</td></tr>
      </tbody></table>

      {{-- Payment schedule: a money table — kept system-computed (no free-form grid). --}}
      <h3>{{ $L('scope.payment_schedule') }}</h3>
      <table class="tbl"><thead><tr><th>{{ $L('scope.milestone') }}</th><th class="desc">{{ $L('scope.trigger') }}</th><th>%</th><th>{{ $L('scope.amount') }} ({{ $cur }})</th></tr></thead><tbody>
        @foreach($quote->milestones as $m)
          <tr><td class="c">{{ $m->code }}</td><td class="desc">{{ $m->triggerLabel() }}</td><td class="c">{{ rtrim(rtrim(number_format($m->percentage,2),'0'),'.') }}%</td><td class="money">{{ number_format($m->amount,0) }}</td></tr>
        @endforeach
      </tbody></table>

      @if(!empty($c['timeline']) || ! empty($quote->custom_tables['timeline'] ?? null))
        @include('scope._table', [
          'slug' => 'timeline',
          'title' => $L('scope.indicative_timeline'),
          'columns' => [$L('scope.period'), $L('scope.activity')],
          'rows' => collect($c['timeline'])->map(fn ($row) => [
              ['t' => $row['period'] ?? ($row['phase'] ?? ''), 'c' => true],
              ['t' => $row['activity'] ?? ($row['notes'] ?? ''), 'cls' => 'desc'],
          ])->all(),
        ])
      @endif
    </div>
  @endunless

  @if(!empty($c['out_of_scope']))
    <div class="section"><h2>{{ $L('scope.out_of_scope') }}</h2>
      <ul class="points">@foreach($c['out_of_scope'] as $line)<li>{{ $line }}</li>@endforeach</ul>
    </div>
  @endif

  @if(!empty($c['notes']))
    <div class="section"><h2>{{ $L('scope.notes') }}</h2>
      <div class="note-block"><ul>@foreach($c['notes'] as $line)<li>{{ $line }}</li>@endforeach</ul></div>
    </div>
  @endif

  {{-- ===== General terms ===== --}}
  <div class="section">
    <h2>{{ $L('scope.general_terms') }}</h2>
    <h3>@lang('scope.client_responsibilities')</h3>
    <ul class="points">@foreach(__('scope.terms_responsibilities') as $x)<li>{{ $x }}</li>@endforeach</ul>
    <h3>@lang('scope.change_requests')</h3><p class="para">@lang('scope.terms_change')</p>
    <h3>@lang('scope.confidentiality')</h3><p class="para">@lang('scope.terms_confidentiality')</p>
    <h3>@lang('scope.validity')</h3><p class="para">@lang('scope.terms_validity', ['days' => $quote->validity_days])</p>
  </div>
@endsection
