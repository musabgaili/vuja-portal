{{--
  Generic document table. Renders the employee's ADVANCED custom grid
  ($quote->custom_tables[$slug]) when present, otherwise the default structured
  table built by the caller. Used for content tables (e.g. the timeline) so the
  employee can fully restructure columns/rows/merges. Money tables never use this.

  Params:
    $slug     string  — key into custom_tables
    $title    string  — heading (already label()-resolved by the caller)
    $columns  array   — default header labels [string,...]
    $rows     array   — default rows: [[ ['t'=>txt,'c'=>bool,'cls'=>'desc|money'], ... ], ...]

  Custom grid shape (custom_tables[$slug]):
    { columns: [{label, align}], rows: [[ {text, colspan, rowspan, align, merged} ]] }
  A cell with merged=true is covered by a neighbour's span and is skipped.
--}}
@php($custom = $quote->custom_tables[$slug] ?? null)
@php($alignClass = fn ($a) => $a === 'center' ? 'c' : ($a === 'end' ? 'money' : ''))
<h3>{{ $title }}</h3>
<table class="tbl">
  @if($custom && ! empty($custom['columns']))
    <thead><tr>
      @foreach($custom['columns'] as $col)
        <th class="{{ $alignClass($col['align'] ?? '') }}">{{ $col['label'] ?? '' }}</th>
      @endforeach
    </tr></thead>
    <tbody>
      @foreach(($custom['rows'] ?? []) as $row)
        <tr>
          @foreach($row as $cell)
            @continue(($cell['merged'] ?? false) === true)
            <td class="{{ $alignClass($cell['align'] ?? '') }}"
                @if((int) ($cell['colspan'] ?? 1) > 1) colspan="{{ (int) $cell['colspan'] }}" @endif
                @if((int) ($cell['rowspan'] ?? 1) > 1) rowspan="{{ (int) $cell['rowspan'] }}" @endif>{{ $cell['text'] ?? '' }}</td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  @else
    <thead><tr>
      @foreach($columns as $col)<th>{{ $col }}</th>@endforeach
    </tr></thead>
    <tbody>
      @foreach($rows as $row)
        <tr>
          @foreach($row as $cell)
            <td class="{{ trim(($cell['cls'] ?? '').' '.(($cell['c'] ?? false) ? 'c' : '')) }}">{{ $cell['t'] ?? '' }}</td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  @endif
</table>
