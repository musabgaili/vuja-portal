{{--
  Scope Planner — shared document frame (layout) for all tiers.
  SOURCE for both the online preview and the PDF. In browser mode the letterhead
  (.lh) and footer (.docfoot) are position:fixed (Chrome repeats them per page);
  in $pdf mode mPDF supplies them (watermark + footer) and we drop the fixed
  layers. Colours are literal (not CSS var()) because mPDF cannot resolve var().
  Measured for THIS letterhead: US Letter, brand #1B565E, safe area 40/18/24 mm.
--}}
@php
    $pdf    = $pdf ?? false;
    $brand  = config('scope.brand_color', '#1B565E');
    $ink    = '#1d2a2c'; $muted = '#5a6a6c';
    $brand2 = '#2c6e76'; $brand3 = '#3a7e86';
    $tint   = '#eaf2f2'; $tint2 = '#f5faf9'; $rule = '#d3dedd';
@endphp
<!DOCTYPE html>
<html lang="{{ $quote->language }}" dir="{{ $quote->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $quote->quote_number }} — {{ $quote->subject }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    @unless($pdf)@page { size: {{ config('scope.page_size', 'letter') }}; margin: 40mm 18mm 24mm; }@endunless
    *{box-sizing:border-box}
    html,body{margin:0;padding:0}
    body{font-family:'Tajawal','Segoe UI',Tahoma,sans-serif;color:{{ $ink }};font-size:12.5px}

    .lh{position:fixed;inset:0;z-index:-1;background:url('{{ asset(config('scope.letterhead', 'images/scope-letterhead.png')) }}') center/cover no-repeat;
        -webkit-print-color-adjust:exact;print-color-adjust:exact}
    .docfoot{position:fixed;left:0;right:0;bottom:11mm;z-index:1;text-align:center;font-size:9.5px;color:{{ $muted }};letter-spacing:.2px}

    .pagebreak{page-break-before:always}

    .meta{width:100%;border-collapse:collapse;margin:0 0 16px;font-size:12.5px}
    .meta td{border:1px solid {{ $rule }};padding:7px 11px;vertical-align:middle;background:#ffffff}
    .meta td.k{background:{{ $tint }};font-weight:700;color:{{ $brand }};width:26%;white-space:nowrap}
    .cover-meta{width:78%;margin:0 auto}

    .section{margin:0 0 13px}
    .section h2{font-size:15px;color:{{ $brand }};font-weight:700;margin:0 0 7px;padding-bottom:4px;border-bottom:1px solid {{ $rule }}}
    .section h3{font-size:12.5px;color:{{ $brand2 }};font-weight:700;margin:9px 0 3px}
    p.para{font-size:12.5px;line-height:1.8;margin:0 0 8px}
    .lead{font-size:12.5px;color:{{ $muted }};margin:0 0 9px}
    ul.points{margin:0;padding:0;list-style:none}
    ul.points li{font-size:12.5px;line-height:1.75;padding:0 18px 3px}

    table.tbl{width:100%;border-collapse:collapse;font-size:11.5px;margin-top:4px;background:#ffffff}
    table.tbl th,table.tbl td{border:1px solid {{ $rule }};padding:7px 9px}
    table.tbl thead th{background:{{ $brand }};color:#fff;font-weight:700;text-align:center}
    table.tbl td.c{text-align:center}
    table.tbl td.money{text-align:center}
    table.tbl td.desc{width:46%}
    table.tbl tr.sub td,table.tbl tr.vat td{background:{{ $tint2 }};font-weight:500}
    table.tbl tr.tot td{background:{{ $tint }};font-weight:700;color:{{ $brand }}}
    table.tbl td.totlabel{text-align:start;font-weight:700}
    .note{font-size:10.5px;color:{{ $muted }};margin-top:5px}
    .note-block{background:{{ $tint2 }};border:1px solid {{ $rule }};border-radius:6px;padding:10px 14px;font-size:12px;line-height:1.7}
    .note-block ul{margin:0;padding-inline-start:18px}
    .note-block li{margin-bottom:3px}

    .cover-title{margin:32mm 0 6mm;text-align:center;font-size:30px;font-weight:700;color:{{ $brand }};letter-spacing:1px}
    .cover-sub{text-align:center;font-size:13px;color:{{ $brand2 }};font-style:italic;margin-bottom:14mm}

    @unless($pdf)
    @media screen{
      body{background:#e7ebe9;padding:32px 16px}
      .sheet{position:relative;width:8.5in;min-height:11in;margin:0 auto;background:#fff;box-shadow:0 10px 40px rgba(0,0,0,.16);overflow:hidden}
      .lh{position:absolute}
      .docfoot{position:absolute}
      .doc-body{position:relative;z-index:1;padding:40mm 18mm 24mm}
    }
    @endunless
  </style>
</head>
<body>
  @if($pdf)
    {{-- mPDF supplies the letterhead (watermark) + footer + page margins. --}}
    <main class="doc-body">@yield('body')</main>
  @else
  <div class="sheet">
    <div class="lh" aria-hidden="true"></div>
    <main class="doc-body">
      @yield('body')
    </main>
    <div class="docfoot">{{ config('scope.footer', 'Vuja De innovations Est. CR: 2251504740 · Riyadh- Airport Road') }}</div>
  </div>
  @endif
</body>
</html>
