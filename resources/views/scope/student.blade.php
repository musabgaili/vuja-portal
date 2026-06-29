{{-- Student tier — concise scope-statement letter. --}}
@extends('scope.document')
@php($c = $quote->ai_content ?? [])
@php($cur = $quote->language === 'ar' ? 'ر.س' : 'SAR')
@php($L = fn ($k) => $quote->label($k, __($k)))

@section('body')
  <table class="meta">
    <tr><td class="k">@lang('scope.to')</td><td>{{ $quote->client_name }}</td></tr>
    <tr><td class="k">@lang('scope.email')</td><td dir="ltr" @if($quote->language==='ar') style="text-align:right" @endif>{{ $quote->client_email }}</td></tr>
    <tr><td class="k">@lang('scope.ref')</td><td dir="ltr" @if($quote->language==='ar') style="text-align:right" @endif>{{ $quote->quote_number }}</td></tr>
    <tr><td class="k">@lang('scope.subject')</td><td>{{ $quote->subject }}</td></tr>
    @if(!empty($c['needs']))<tr><td class="k">@lang('scope.needs')</td><td>{{ $c['needs'] }}</td></tr>@endif
  </table>

  <div class="section">
    <h2>{{ $L('scope.scope_of_work') }}</h2>
    <ul class="points">
      @foreach(($c['scope_of_work'] ?? []) as $line)<li>{{ $line }}</li>@endforeach
    </ul>
  </div>

  <div class="section">
    <h2>{{ $L('scope.pricing') }}</h2>
    <p class="lead">@lang('scope.pricing_intro')</p>
    <table class="tbl">
      <thead><tr>
        <th style="width:6%">@lang('scope.item_no')</th>
        <th class="desc">{{ $L('scope.description') }}</th>
        <th style="width:10%">{{ $L('scope.unit') }}</th>
        <th style="width:10%">{{ $L('scope.qty') }}</th>
        <th style="width:14%">{{ $L('scope.unit_price') }} ({{ $cur }})</th>
        <th style="width:14%">{{ $L('scope.total') }} ({{ $cur }})</th>
      </tr></thead>
      <tbody>
        @foreach($quote->clientVisibleItems() as $i => $row)
          <tr>
            <td class="c">{{ $i + 1 }}</td>
            <td class="desc">{{ $row->description }}@if($row->details ?? null)<br><small style="color:#64748b">{{ $row->details }}</small>@endif</td>
            <td class="c">{{ $row->unit ?? __('scope.all') }}</td>
            <td class="c">{{ rtrim(rtrim(number_format($row->quantity,2),'0'),'.') }}</td>
            <td class="money">{{ number_format($row->unit_price,0) }}</td>
            <td class="money">{{ number_format($row->line_total,0) }}</td>
          </tr>
        @endforeach
        <tr class="sub"><td class="totlabel" colspan="5">@lang('scope.subtotal')</td><td class="money">{{ number_format($quote->subtotal,0) }}</td></tr>
        <tr class="vat"><td class="totlabel" colspan="5">@lang('scope.vat', ['rate' => rtrim(rtrim(number_format($quote->vat_rate,2),'0'),'.')])</td><td class="money">{{ number_format($quote->vat_amount,0) }}</td></tr>
        <tr class="tot"><td class="totlabel" colspan="5">@lang('scope.grand_total')</td><td class="money">{{ number_format($quote->grand_total,0) }}</td></tr>
      </tbody>
    </table>
    <p class="note">@lang('scope.components_note')</p>
  </div>

  <div class="section">
    <h2>{{ $L('scope.out_of_scope') }}</h2>
    <ul class="points">
      @foreach(($c['out_of_scope'] ?? []) as $line)<li>{{ $line }}</li>@endforeach
    </ul>
  </div>

  <div class="section">
    <h2>{{ $L('scope.notes') }}</h2>
    <div class="note-block"><ul>
      @foreach(($c['notes'] ?? []) as $line)<li>{{ $line }}</li>@endforeach
    </ul></div>
  </div>
@endsection
