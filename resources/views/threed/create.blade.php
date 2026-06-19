@extends('layouts.dashboard')

@section('title', __('portal.threed.create.title'))
@section('page-title', __('portal.threed.create.title'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cubes"></i> {{ __('portal.threed.create.heading') }}</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('portal.threed.back') }}
        </a>
    </div>
    <div class="card-content">
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>
        @endif

        <form method="POST" action="{{ route('threed.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Type switch --}}
            <input type="hidden" name="type" id="threed_type" value="{{ old('type', $type) }}">
            <div class="form-group">
                <label class="form-label">{{ __('portal.threed.field.type') }} *</label>
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn td-type-btn" data-type="printing" aria-pressed="false" onclick="setThreedType('printing')">
                        <i class="fas fa-check td-check"></i> <i class="fas fa-print"></i> {{ __('portal.threed.type.printing') }}
                    </button>
                    <button type="button" class="btn td-type-btn" data-type="design" aria-pressed="false" onclick="setThreedType('design')">
                        <i class="fas fa-check td-check"></i> <i class="fas fa-pen-ruler"></i> {{ __('portal.threed.type.design') }}
                    </button>
                </div>
                <small class="text-muted d-block mt-1" id="td-type-hint"></small>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.threed.field.title') }} *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="200"
                       placeholder="{{ __('portal.threed.field.title_ph') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.threed.field.description') }} *</label>
                <textarea name="description" rows="5" class="form-control" required
                          placeholder="{{ __('portal.threed.field.description_ph') }}">{{ old('description') }}</textarea>
            </div>

            {{-- ===== PRINTING fields ===== --}}
            <div class="td-fields td-printing">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('portal.threed.field.material') }}</label>
                            <select name="material" class="form-control">
                                <option value="">{{ __('portal.threed.field.select') }}</option>
                                @foreach($materials as $m)
                                    <option value="{{ $m }}" @selected(old('material') === $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('portal.threed.field.color') }}</label>
                            <input type="text" name="color" class="form-control" value="{{ old('color') }}"
                                   placeholder="{{ __('portal.threed.field.color_ph') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('portal.threed.field.quantity') }}</label>
                            <input type="number" name="quantity" min="1" class="form-control" value="{{ old('quantity') }}" placeholder="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('portal.threed.field.dimensions') }}</label>
                            <input type="text" name="dimensions" class="form-control" value="{{ old('dimensions') }}"
                                   placeholder="{{ __('portal.threed.field.dimensions_ph') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('portal.threed.field.finish') }}</label>
                            <input type="text" name="finish" class="form-control" value="{{ old('finish') }}"
                                   placeholder="{{ __('portal.threed.field.finish_ph') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== DESIGN fields ===== --}}
            <div class="td-fields td-design">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('portal.threed.field.output_format') }}</label>
                            <select name="output_format" class="form-control">
                                <option value="">{{ __('portal.threed.field.select') }}</option>
                                @foreach($outputFormats as $f)
                                    <option value="{{ $f }}" @selected(old('output_format') === $f)>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('portal.threed.field.complexity') }}</label>
                            <select name="complexity" class="form-control">
                                <option value="">{{ __('portal.threed.field.select') }}</option>
                                @foreach($complexities as $c)
                                    <option value="{{ $c }}" @selected(old('complexity') === $c)>{{ __('portal.threed.complexity.'.$c) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('portal.threed.field.reference_links') }}</label>
                    <textarea name="reference_links" rows="2" class="form-control"
                              placeholder="{{ __('portal.threed.field.reference_links_ph') }}">{{ old('reference_links') }}</textarea>
                </div>
            </div>

            {{-- ===== Shared ===== --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.threed.field.budget') }}</label>
                        <input type="text" name="budget_range" class="form-control" value="{{ old('budget_range') }}"
                               placeholder="{{ __('portal.threed.field.budget_ph') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.threed.field.timeline') }}</label>
                        <input type="text" name="timeline" class="form-control" value="{{ old('timeline') }}"
                               placeholder="{{ __('portal.threed.field.timeline_ph') }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.threed.field.files') }}</label>
                <input type="file" name="files[]" class="form-control" multiple
                       accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.zip,.txt,.csv,.svg,.stl,.obj,.step,.stp,.3mf,.fbx,.dwg,.dxf">
                <small class="text-muted" id="td-files-hint">{{ __('portal.threed.field.files_hint_printing') }}</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>{{ __('portal.threed.create.what_next') }}:</strong> {{ __('portal.threed.create.what_next_body') }}
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.threed.create.submit') }}</button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> {{ __('portal.threed.create.cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<style>
.td-type-btn { border: 1px solid var(--border-color, #cbd5e1); background: transparent; color: var(--text-color, #334155); }
.td-type-btn.active { background: var(--primary-color, #0C7075); color: #fff; border-color: var(--primary-color, #0C7075); }
.td-type-btn .td-check { display: none; }
.td-type-btn.active .td-check { display: inline-block; }
.td-fields { display: none; }
.td-fields.active { display: block; }
</style>

@push('scripts')
<script>
var TD_HINTS = {
    printing: @json(__('portal.threed.type.printing_hint')),
    design: @json(__('portal.threed.type.design_hint')),
};
var TD_FILES = {
    printing: @json(__('portal.threed.field.files_hint_printing')),
    design: @json(__('portal.threed.field.files_hint_design')),
};
function setThreedType(t) {
    document.getElementById('threed_type').value = t;
    document.querySelectorAll('.td-type-btn').forEach(function (b) {
        var on = b.dataset.type === t;
        b.classList.toggle('active', on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    document.querySelector('.td-printing').classList.toggle('active', t === 'printing');
    document.querySelector('.td-design').classList.toggle('active', t === 'design');
    document.getElementById('td-type-hint').textContent = TD_HINTS[t] || '';
    document.getElementById('td-files-hint').textContent = TD_FILES[t] || '';
}
document.addEventListener('DOMContentLoaded', function () {
    setThreedType(document.getElementById('threed_type').value || 'printing');
});
</script>
@endpush
@endsection
