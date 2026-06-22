{{-- Reusable "add a new client" modal.
     Include once on any page that has a client <select>:
         @include('partials.quick-add-client')
     and trigger it from a button, passing the id of the target select:
         <button type="button" onclick="quickAddClient('client_id')">+ New client</button>
     On success the new client is appended to that <select> and selected. --}}
<div class="modal fade" id="quickClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="quickClientForm" data-no-submit-guard>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> {{ __('portal.quick_client.title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('portal.team.cancel') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.9rem;">{{ __('portal.quick_client.intro') }}</p>
                    <div class="form-group mb-2">
                        <label>{{ __('portal.quick_client.name') }} *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>{{ __('portal.quick_client.email') }} *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-2">
                            <label>{{ __('portal.quick_client.phone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label>{{ __('portal.quick_client.company') }}</label>
                            <input type="text" name="company" class="form-control">
                        </div>
                    </div>
                    <div class="form-check mt-1">
                        <input type="checkbox" class="form-check-input" name="invite" value="1" id="quickClientInvite">
                        <label class="form-check-label" for="quickClientInvite" style="font-size:.9rem;">{{ __('portal.quick_client.invite_label') }}</label>
                    </div>
                    <div class="alert alert-danger d-none" id="quickClientError" style="font-size:.85rem;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" data-no-submit-guard>
                        <i class="fas fa-save"></i> {{ __('portal.quick_client.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var modalEl = document.getElementById('quickClientModal');
    if (!modalEl) { return; }
    var form = document.getElementById('quickClientForm');
    var errBox = document.getElementById('quickClientError');
    var targetSelectId = null;

    window.quickAddClient = function (selectId) {
        targetSelectId = selectId;
        errBox.classList.add('d-none');
        errBox.textContent = '';
        form.reset();
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    function showError(msg) {
        errBox.textContent = msg || '{{ __('portal.quick_client.error') }}';
        errBox.classList.remove('d-none');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        errBox.classList.add('d-none');

        fetch('{{ route('clients.quick-store') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form),
        }).then(function (r) {
            return r.json().then(function (body) { return { ok: r.ok, body: body }; });
        }).then(function (res) {
            if (!res.ok) {
                var msg = res.body && res.body.message ? res.body.message : null;
                if (res.body && res.body.errors) {
                    msg = Object.keys(res.body.errors).map(function (k) { return res.body.errors[k][0]; }).join(' ');
                }
                showError(msg);
                btn.disabled = false;
                return;
            }
            var c = res.body;
            var sel = targetSelectId ? document.getElementById(targetSelectId) : null;
            if (sel) {
                if (!sel.querySelector('option[value="' + c.id + '"]')) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name + ' (' + c.email + ')';
                    sel.appendChild(opt);
                }
                sel.value = String(c.id);
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (typeof window.showAppToast === 'function') {
                var msg = c.reused ? '{{ __('portal.quick_client.reused') }}' : '{{ __('portal.quick_client.created') }}';
                if (c.invited) { msg += ' ' + '{{ __('portal.quick_client.invited') }}'; }
                window.showAppToast(msg, 'success');
            }
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            btn.disabled = false;
        }).catch(function () {
            showError('{{ __('portal.quick_client.error') }}');
            btn.disabled = false;
        });
    });
})();
</script>
@endpush
