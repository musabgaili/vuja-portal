{{-- One editable Company scope block. $key = an existing scope id or a "new_*"
     placeholder (the controller creates new_* rows and deletes any omitted id). --}}
@php($ed = $editable ?? true)
<div class="border rounded p-2 mb-3 scope-row">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="form-label fw-bold m-0">{{ __('portal.scope_planner.scope_title') }}</label>
        @if($ed)<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.scope-row').remove()" title="{{ __('portal.scope_planner.remove_scope') }}"><i class="fas fa-trash"></i></button>@endif
    </div>
    <input type="text" name="scopes[{{ $key }}][title]" value="{{ $scope?->title ?? '' }}" class="form-control form-control-sm mb-2" placeholder="{{ __('portal.scope_planner.scope_title') }}" @unless($ed)readonly @endunless>
    @foreach(['objective','inputs_required','deliverables','acceptance_criteria','exclusions'] as $f)
        <label class="form-label small mb-0">{{ __('scope.'.$f) }} <small class="text-muted">({{ __('portal.scope_planner.one_per_line') }})</small></label>
        <textarea name="scopes[{{ $key }}][{{ $f }}]" data-autosize rows="2" class="form-control form-control-sm mb-2" @unless($ed)readonly @endunless>{{ isset($scope) ? implode("\n", (array) (is_array($scope->$f) ? $scope->$f : array_filter([$scope->$f]))) : '' }}</textarea>
    @endforeach
</div>
