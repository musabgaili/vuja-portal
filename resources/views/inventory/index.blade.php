@extends('layouts.internal-dashboard')
@section('title', __('portal.inventory.title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-boxes-stacked"></i> {{ __('portal.inventory.title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.inventory.subtitle') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('inventory.create') }}" class="btn btn-light"><i class="fas fa-plus"></i> {{ __('portal.inventory.add_item') }}</a>
        <a href="{{ route('inventory.import.create') }}" class="btn btn-light"><i class="fas fa-file-excel"></i> {{ __('portal.inventory.upload_excel') }}</a>
        <a href="{{ route('inventory.export') }}" class="btn btn-light"><i class="fas fa-download"></i> {{ __('portal.inventory.export') }}</a>
    </div>
</div>

@include('inventory._messages')

<div class="card mb-3">
    <div class="card-content">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('portal.inventory.search') }}</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('portal.inventory.search_ph') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('portal.inventory.lab') }}</label>
                <select name="lab" class="form-select form-select-sm">
                    <option value="">{{ __('portal.inventory.all_labs') }}</option>
                    @foreach ($labs as $lab)
                        <option value="{{ $lab->id }}" @selected(request('lab') == $lab->id)>{{ $lab->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i> {{ __('portal.inventory.filter') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-content p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:48px;"></th>
                        <th>{{ __('portal.inventory.product_id') }}</th>
                        <th>{{ __('portal.inventory.name') }}</th>
                        <th>{{ __('portal.inventory.stock_by_lab') }}</th>
                        <th class="text-end">{{ __('portal.inventory.total') }}</th>
                        <th class="text-end">{{ __('portal.inventory.purchase') }}</th>
                        <th class="text-end">{{ __('portal.inventory.student') }}</th>
                        <th class="text-end">{{ __('portal.inventory.entrepreneur') }}</th>
                        <th class="text-end">{{ __('portal.inventory.company') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px">
                                @else
                                    <span class="text-muted"><i class="fas fa-image"></i></span>
                                @endif
                            </td>
                            <td><code>{{ $item->product_id }}</code>@unless($item->is_active)<span class="badge bg-secondary ms-1">{{ __('portal.inventory.inactive') }}</span>@endunless</td>
                            <td>{{ $item->name }}@if($item->category)<div class="text-muted" style="font-size:.75rem;">{{ $item->category }}</div>@endif</td>
                            <td>
                                @foreach ($item->labs as $lab)
                                    <span class="badge bg-light text-dark border">{{ $lab->name }}: {{ $lab->pivot->quantity }}</span>
                                @endforeach
                            </td>
                            <td class="text-end"><strong>{{ $item->total_stock }}</strong></td>
                            <td class="text-end">{{ number_format($item->purchase_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->price_student, 2) }}</td>
                            <td class="text-end">{{ number_format($item->price_entrepreneur, 2) }}</td>
                            <td class="text-end">{{ number_format($item->price_company, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('inventory.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('inventory.destroy', $item) }}" class="d-inline" onsubmit="return confirm('{{ __('portal.inventory.delete_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">{{ __('portal.inventory.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
