@extends('layouts.internal-dashboard')
@section('title', __('portal.chat.title'))

@php
    $me = auth()->user();
    $textChannels = $channels->where('type', 'channel');
    $dmChannels = $channels->where('type', 'dm');
    $lastId = $messages->max('id') ?? 0;
    $firstId = $messages->min('id') ?? 0;
@endphp

@section('content')
<div class="chat-wrap"
     data-poll-url="{{ route('chat.poll') }}"
     data-members-url="{{ route('chat.members') }}"
     data-msg-base="{{ url('internal/chat/messages') }}">

    {{-- ===== Conversations sidebar ===== --}}
    <aside class="chat-sidebar" id="chatSidebar">
        <div class="chat-side-head">
            <span><i class="fas fa-comments"></i> {{ __('portal.chat.title') }}</span>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#newDmModal" title="{{ __('portal.chat.new_dm') }}"><i class="fas fa-user-plus"></i></button>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newChannelModal" title="{{ __('portal.chat.new_channel') }}"><i class="fas fa-plus"></i></button>
            </div>
        </div>

        <div class="chat-side-section">{{ __('portal.chat.channels') }}</div>
        @forelse($textChannels as $c)
            <a href="{{ route('chat.show', $c) }}" class="chat-conv {{ $channel && $channel->id === $c->id ? 'active' : '' }}">
                <span class="chat-conv-name"># {{ $c->displayName($me) }}</span>
                <span class="chat-unread" data-channel="{{ $c->id }}" hidden>0</span>
            </a>
        @empty
            <div class="chat-side-empty">{{ __('portal.chat.no_channels') }}</div>
        @endforelse

        <div class="chat-side-section">{{ __('portal.chat.direct_messages') }}</div>
        @forelse($dmChannels as $c)
            <a href="{{ route('chat.show', $c) }}" class="chat-conv {{ $channel && $channel->id === $c->id ? 'active' : '' }}">
                <span class="chat-conv-name"><i class="fas fa-at"></i> {{ $c->displayName($me) }}</span>
                <span class="chat-unread" data-channel="{{ $c->id }}" hidden>0</span>
            </a>
        @empty
            <div class="chat-side-empty">{{ __('portal.chat.no_dms') }}</div>
        @endforelse
    </aside>

    {{-- ===== Active conversation ===== --}}
    <main class="chat-main">
        @if($channel)
            <header class="chat-head">
                <button type="button" class="btn btn-sm btn-light chat-side-toggle d-lg-none" id="chatSideToggle"><i class="fas fa-bars"></i></button>
                <div>
                    <strong>{{ $channel->isDm() ? '' : '# ' }}{{ $channel->displayName($me) }}</strong>
                    @if($channel->description)<div class="chat-head-desc">{{ $channel->description }}</div>@endif
                </div>
                @if($channel->isDm())
                <span class="chat-head-meta">{{ $channel->members->count() }} {{ __('portal.chat.members') }}</span>
                @else
                <button type="button" class="btn btn-sm btn-light chat-head-meta" data-bs-toggle="modal" data-bs-target="#manageMembersModal" title="{{ __('portal.chat.manage_members') }}">
                    <i class="fas fa-users"></i> {{ $channel->members->count() }} {{ __('portal.chat.members') }}
                </button>
                @endif
            </header>

            <div id="chatStream" class="chat-stream"
                 data-channel="{{ $channel->id }}"
                 data-messages-url="{{ route('chat.messages', $channel) }}"
                 data-older-url="{{ route('chat.older', $channel) }}"
                 data-thread-base="{{ url('internal/chat/'.$channel->id.'/thread') }}"
                 data-last="{{ $lastId }}"
                 data-first="{{ $firstId }}"
                 data-since="{{ now()->timestamp }}">
                <button type="button" id="chatLoadEarlier" class="btn btn-sm btn-light chat-load-earlier" {{ $messages->count() < \App\Http\Controllers\ChatController::PAGE_SIZE ? 'hidden' : '' }}>
                    <i class="fas fa-arrow-up"></i> {{ __('portal.chat.load_earlier') }}
                </button>
                @forelse($messages as $m)
                    @include('chat._message', ['m' => $m, 'emojis' => \App\Http\Controllers\ChatController::EMOJIS, 'reads' => $readState['reads'] ?? [], 'memberCount' => $readState['memberCount'] ?? 0])
                @empty
                    <div class="chat-empty-stream" id="chatEmptyStream">{{ __('portal.chat.no_messages') }}</div>
                @endforelse
            </div>

            <form class="chat-composer" id="chatComposer" method="POST" action="{{ route('chat.messages.store', $channel) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="parent_id" value="">
                <div class="chat-composer-files" id="chatComposerFiles"></div>
                <div class="chat-composer-row">
                    <label class="chat-attach-btn" title="{{ __('portal.chat.attach') }}">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="attachments[]" multiple hidden id="chatFiles"
                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip">
                    </label>
                    <textarea name="body" id="chatBody" rows="1" class="form-control" placeholder="{{ __('portal.chat.message_placeholder') }}" autocomplete="off"></textarea>
                    <button type="submit" class="btn btn-primary chat-send"><i class="fas fa-paper-plane"></i></button>
                </div>
                <div class="chat-mention-pop" id="chatMentionPop" hidden></div>
            </form>
        @else
            <div class="chat-empty">
                <i class="fas fa-comments"></i>
                <h4>{{ __('portal.chat.empty_title') }}</h4>
                <p class="text-muted">{{ __('portal.chat.empty_body') }}</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newChannelModal"><i class="fas fa-plus"></i> {{ __('portal.chat.new_channel') }}</button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newDmModal"><i class="fas fa-user-plus"></i> {{ __('portal.chat.new_dm') }}</button>
                </div>
            </div>
        @endif
    </main>

    {{-- ===== Thread pane ===== --}}
    <aside class="chat-thread" id="chatThread" hidden>
        <div class="chat-thread-head">
            <strong>{{ __('portal.chat.thread') }}</strong>
            <button type="button" class="btn btn-sm btn-light" id="chatThreadClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="chat-thread-body" id="chatThreadBody"></div>
        @if($channel)
        <form class="chat-thread-composer" id="chatThreadComposer" method="POST" action="{{ route('chat.messages.store', $channel) }}">
            @csrf
            <input type="hidden" name="parent_id" id="chatThreadParent" value="">
            <textarea name="body" rows="1" class="form-control" placeholder="{{ __('portal.chat.reply_placeholder') }}"></textarea>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i></button>
        </form>
        @endif
    </aside>
</div>

{{-- ===== Modals ===== --}}
<div class="modal fade" id="newChannelModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('chat.channels.store') }}">
      @csrf
      <div class="modal-header"><h5 class="modal-title">{{ __('portal.chat.new_channel') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">{{ __('portal.chat.channel_name') }} *</label>
          <input type="text" name="name" class="form-control" required maxlength="80">
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __('portal.chat.description') }}</label>
          <input type="text" name="description" class="form-control" maxlength="255">
        </div>
        <div class="form-check mb-2">
          <input type="checkbox" class="form-check-input" name="is_private" value="1" id="chPrivate">
          <label class="form-check-label" for="chPrivate">{{ __('portal.chat.private_channel') }}</label>
        </div>
        <label class="form-label">{{ __('portal.chat.add_members') }}</label>
        <div class="chat-member-pick">
          @foreach($allUsers as $u)
            <label class="chat-member-opt"><input type="checkbox" name="members[]" value="{{ $u->id }}"> {{ $u->name }}</label>
          @endforeach
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">{{ __('portal.chat.create') }}</button></div>
    </form>
  </div></div>
</div>

<div class="modal fade" id="newDmModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('chat.dm') }}">
      @csrf
      <div class="modal-header"><h5 class="modal-title">{{ __('portal.chat.new_dm') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p class="text-muted">{{ __('portal.chat.dm_help') }}</p>
        <div class="chat-member-pick">
          @foreach($allUsers as $u)
            <label class="chat-member-opt"><input type="checkbox" name="members[]" value="{{ $u->id }}"> {{ $u->name }}</label>
          @endforeach
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">{{ __('portal.chat.open_chat') }}</button></div>
    </form>
  </div></div>
</div>

@if($channel && ! $channel->isDm())
<div class="modal fade" id="manageMembersModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title"><i class="fas fa-users"></i> {{ __('portal.chat.manage_members') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <h6 style="font-size:.9rem;">{{ __('portal.chat.current_members') }}</h6>
      <ul class="list-unstyled" style="max-height:240px;overflow-y:auto;margin-bottom:0;">
        @foreach($members as $m)
        <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
          <span>{{ $m->name }}@if($m->id === $channel->created_by) <small class="text-muted">({{ __('portal.chat.owner') }})</small>@endif</span>
          @can('manageMembers', $channel)
            @if($m->id !== $channel->created_by)
            <form method="POST" action="{{ route('chat.members.remove', [$channel, $m]) }}" onsubmit="return confirm('{{ __('portal.chat.confirm_remove') }}');" class="m-0">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('portal.chat.remove') }}"><i class="fas fa-user-minus"></i></button>
            </form>
            @endif
          @endcan
        </li>
        @endforeach
      </ul>

      @can('manageMembers', $channel)
        @php $addable = $allUsers->whereNotIn('id', $members->pluck('id')); @endphp
        @if($addable->isNotEmpty())
        <hr>
        <form method="POST" action="{{ route('chat.members.add', $channel) }}">
          @csrf
          <h6 style="font-size:.9rem;">{{ __('portal.chat.add_members') }}</h6>
          <div class="chat-member-pick">
            @foreach($addable as $u)
            <label class="chat-member-opt"><input type="checkbox" name="members[]" value="{{ $u->id }}"> {{ $u->name }}</label>
            @endforeach
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-2"><i class="fas fa-user-plus"></i> {{ __('portal.chat.add_selected') }}</button>
        </form>
        @else
        <hr>
        <p class="text-muted" style="font-size:.85rem;margin-bottom:0;">{{ __('portal.chat.all_added') }}</p>
        @endif
      @endcan
    </div>
  </div></div>
</div>
@endif
@endsection

@push('styles')
<style>
.chat-wrap{display:flex;height:calc(100vh - 150px);min-height:480px;background:var(--bg-secondary,#fff);border:1px solid var(--gray-200,#e2e8f0);border-radius:14px;overflow:hidden;}
.chat-sidebar{width:264px;flex-shrink:0;border-inline-end:1px solid var(--gray-200,#e2e8f0);overflow-y:auto;background:var(--bg-tertiary,#f8fafc);}
.chat-side-head{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1rem;font-weight:700;border-bottom:1px solid var(--gray-200,#e2e8f0);}
.chat-side-section{padding:.6rem 1rem .25rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-500,#94a3b8);font-weight:700;}
.chat-conv{display:flex;justify-content:space-between;align-items:center;gap:.5rem;padding:.5rem 1rem;color:var(--text-color,#334155);text-decoration:none;font-size:.92rem;}
.chat-conv:hover{background:var(--gray-100,#f1f5f9);}
.chat-conv.active{background:var(--primary-color,#0C7075);color:#fff;}
.chat-conv-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.chat-unread{background:#ef4444;color:#fff;border-radius:999px;font-size:.7rem;padding:0 .4rem;min-width:18px;text-align:center;}
.chat-side-empty{padding:.25rem 1rem .75rem;color:var(--gray-500,#94a3b8);font-size:.82rem;}
.chat-main{flex:1;display:flex;flex-direction:column;min-width:0;}
.chat-head{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-bottom:1px solid var(--gray-200,#e2e8f0);}
.chat-head-desc{font-size:.8rem;color:var(--gray-500,#94a3b8);}
.chat-head-meta{margin-inline-start:auto;font-size:.8rem;color:var(--gray-500,#94a3b8);}
.chat-stream{flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.25rem;}
.chat-empty-stream{margin:auto;color:var(--gray-500,#94a3b8);}
.chat-load-earlier{display:block;margin:0 auto .5rem;}
.chat-msg{display:flex;gap:.65rem;padding:.4rem .25rem;border-radius:8px;}
.chat-msg:hover{background:var(--gray-50,#f8fafc);}
.chat-msg:hover .chat-msg-foot{opacity:1;}
.chat-msg-avatar{width:36px;height:36px;flex-shrink:0;border-radius:50%;background:var(--primary-color,#0C7075);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;text-transform:uppercase;}
.chat-msg-main{min-width:0;flex:1;}
.chat-msg-head{display:flex;gap:.5rem;align-items:baseline;}
.chat-msg-time,.chat-msg-edited{font-size:.72rem;color:var(--gray-500,#94a3b8);}
.chat-msg-body{white-space:pre-wrap;word-break:break-word;line-height:1.45;}
.chat-mention{background:rgba(12,112,117,.14);color:var(--primary-color,#0C7075);border-radius:4px;padding:0 .2rem;font-weight:600;}
.chat-att{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.4rem;}
.chat-att-img img{max-width:220px;max-height:180px;border-radius:8px;display:block;}
.chat-att-file{display:inline-flex;align-items:center;gap:.4rem;background:var(--gray-100,#f1f5f9);padding:.35rem .6rem;border-radius:8px;font-size:.85rem;text-decoration:none;color:var(--text-color,#334155);}
.chat-msg-foot{display:flex;align-items:center;gap:.35rem;margin-top:.3rem;opacity:.55;transition:opacity .15s;flex-wrap:wrap;}
.chat-msg-foot button,.chat-del-btn{background:none;border:1px solid var(--gray-200,#e2e8f0);border-radius:999px;font-size:.78rem;padding:.05rem .5rem;color:var(--gray-600,#475569);cursor:pointer;}
.chat-seen{font-size:.7rem;color:var(--gray-500,#94a3b8);margin-top:.15rem;}
.chat-seen .fa-check-double{color:var(--primary-color,#0C7075);}
.chat-react.on{background:rgba(12,112,117,.14);border-color:var(--primary-color,#0C7075);color:var(--primary-color,#0C7075);}
.chat-del-form{display:inline;}
.chat-react-add{position:relative;cursor:pointer;color:var(--gray-500,#94a3b8);padding:.05rem .3rem;}
.chat-emoji-pop{display:none;position:absolute;bottom:120%;inset-inline-start:0;background:var(--bg-secondary,#fff);border:1px solid var(--gray-200,#e2e8f0);border-radius:8px;padding:.25rem;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:20;white-space:nowrap;}
.chat-react-add:hover .chat-emoji-pop,.chat-react-add:focus-within .chat-emoji-pop{display:block;}
.chat-react-pick{border:none;background:none;font-size:1.05rem;cursor:pointer;padding:.1rem .2rem;}
.chat-composer{border-top:1px solid var(--gray-200,#e2e8f0);padding:.6rem .85rem;position:relative;}
.chat-composer-row{display:flex;gap:.5rem;align-items:flex-end;}
.chat-attach-btn{cursor:pointer;color:var(--gray-500,#94a3b8);padding:.45rem .3rem;}
.chat-composer textarea{resize:none;max-height:140px;}
.chat-composer-files{display:flex;flex-wrap:wrap;gap:.4rem;}
.chat-composer-files .f{font-size:.78rem;background:var(--gray-100,#f1f5f9);border-radius:6px;padding:.1rem .45rem;}
.chat-mention-pop{position:absolute;bottom:100%;inset-inline-start:.85rem;background:var(--bg-secondary,#fff);border:1px solid var(--gray-200,#e2e8f0);border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.14);max-height:220px;overflow:auto;z-index:30;min-width:200px;}
.chat-mention-pop .opt{padding:.4rem .7rem;cursor:pointer;font-size:.9rem;}
.chat-mention-pop .opt.sel,.chat-mention-pop .opt:hover{background:var(--primary-color,#0C7075);color:#fff;}
.chat-empty{margin:auto;text-align:center;color:var(--gray-500,#94a3b8);padding:2rem;}
.chat-empty i{font-size:3rem;opacity:.4;margin-bottom:.75rem;}
.chat-thread{width:340px;flex-shrink:0;border-inline-start:1px solid var(--gray-200,#e2e8f0);display:flex;flex-direction:column;background:var(--bg-secondary,#fff);}
.chat-thread-head{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid var(--gray-200,#e2e8f0);}
.chat-thread-body{flex:1;overflow-y:auto;padding:.75rem;}
.chat-thread-composer{display:flex;gap:.4rem;padding:.6rem;border-top:1px solid var(--gray-200,#e2e8f0);}
.chat-member-pick{max-height:240px;overflow:auto;border:1px solid var(--gray-200,#e2e8f0);border-radius:8px;padding:.5rem;display:flex;flex-direction:column;gap:.25rem;}
.chat-member-opt{font-size:.9rem;cursor:pointer;}
.chat-edit-area textarea{width:100%;}
@media (max-width:991px){
  .chat-sidebar{position:absolute;z-index:40;height:100%;inset-inline-start:-280px;transition:inset-inline-start .2s;box-shadow:0 0 24px rgba(0,0,0,.15);}
  .chat-sidebar.open{inset-inline-start:0;}
  .chat-thread{position:absolute;inset-inline-end:0;height:100%;width:100%;max-width:380px;z-index:45;}
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const wrap = document.querySelector('.chat-wrap');
    if (!wrap) return;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const pollUrl = wrap.dataset.pollUrl;
    const membersUrl = wrap.dataset.membersUrl;
    const msgBase = wrap.dataset.msgBase;
    const stream = document.getElementById('chatStream');
    const headers = { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
    const L_SEEN = @js(__('portal.chat.seen'));
    const L_SENT = @js(__('portal.chat.sent'));
    const L_SEEN_BY = @js(__('portal.chat.seen_by'));

    // Live-update the "Seen / Seen by N" receipts on my own messages from the poll's reads map.
    function updateSeen(reads, memberCount) {
        if (!stream || !memberCount || memberCount < 2) return;
        const vals = Object.values(reads || {});
        const others = memberCount - 1;
        const dbl = '<i class="fas fa-check-double"></i> ';
        const one = '<i class="fas fa-check"></i> ';
        stream.querySelectorAll('.chat-seen').forEach(el => {
            const mid = parseInt(el.dataset.mid || '0', 10);
            const seenBy = vals.filter(v => parseInt(v, 10) >= mid).length;
            if (memberCount === 2) {
                el.innerHTML = seenBy >= 1 ? (dbl + L_SEEN) : (one + L_SENT);
            } else {
                el.innerHTML = seenBy > 0 ? (dbl + L_SEEN_BY + ' ' + seenBy + '/' + others) : (one + L_SENT);
            }
        });
    }

    function nearBottom() { return stream && (stream.scrollHeight - stream.scrollTop - stream.clientHeight < 120); }
    function toBottom() { if (stream) stream.scrollTop = stream.scrollHeight; }
    if (stream) toBottom();

    // Sidebar drawer (mobile)
    const sideToggle = document.getElementById('chatSideToggle');
    if (sideToggle) sideToggle.addEventListener('click', () => document.getElementById('chatSidebar').classList.toggle('open'));

    // ---- Composer: autosize ----
    const body = document.getElementById('chatBody');
    function autosize(t) { t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight, 140) + 'px'; }
    if (body) body.addEventListener('input', () => autosize(body));

    // ---- Attachment filename preview ----
    const fileInput = document.getElementById('chatFiles');
    const fileBox = document.getElementById('chatComposerFiles');
    if (fileInput) fileInput.addEventListener('change', function () {
        fileBox.innerHTML = '';
        Array.from(fileInput.files).forEach(f => {
            const s = document.createElement('span'); s.className = 'f'; s.textContent = f.name; fileBox.appendChild(s);
        });
    });

    // ---- @mention autocomplete ----
    const pop = document.getElementById('chatMentionPop');
    let mentionResults = [], mentionSel = 0, mentionStart = -1;
    async function onMentionType() {
        if (!stream) return;
        const val = body.value, pos = body.selectionStart;
        const m = val.slice(0, pos).match(/@(\S*)$/);
        if (!m) { hideMention(); return; }
        mentionStart = pos - m[1].length - 1;
        try {
            const url = membersUrl + '?channel=' + stream.dataset.channel + '&q=' + encodeURIComponent(m[1]);
            const res = await fetch(url, { headers }); mentionResults = await res.json();
        } catch (e) { mentionResults = []; }
        renderMention();
    }
    function renderMention() {
        if (!mentionResults.length) { hideMention(); return; }
        mentionSel = 0;
        pop.innerHTML = mentionResults.map((u, i) =>
            '<div class="opt' + (i === 0 ? ' sel' : '') + '" data-name="' + u.name.replace(/"/g, '&quot;') + '">' +
            u.name.replace(/</g, '&lt;') + '</div>').join('');
        pop.hidden = false;
    }
    function hideMention() { pop.hidden = true; mentionResults = []; }
    function pickMention(name) {
        const pos = body.selectionStart;
        body.value = body.value.slice(0, mentionStart) + '@' + name + ' ' + body.value.slice(pos);
        hideMention(); body.focus();
        const caret = mentionStart + name.length + 2; body.setSelectionRange(caret, caret);
    }
    if (body) {
        body.addEventListener('input', onMentionType);
        body.addEventListener('keydown', function (e) {
            if (pop.hidden) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); mentionSel = Math.min(mentionSel + 1, mentionResults.length - 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); mentionSel = Math.max(mentionSel - 1, 0); }
            else if (e.key === 'Enter' && mentionResults.length) { e.preventDefault(); pickMention(mentionResults[mentionSel].name); return; }
            else if (e.key === 'Escape') { hideMention(); return; }
            else return;
            pop.querySelectorAll('.opt').forEach((o, i) => o.classList.toggle('sel', i === mentionSel));
        });
        pop.addEventListener('mousedown', function (e) {
            const opt = e.target.closest('.opt'); if (opt) { e.preventDefault(); pickMention(opt.dataset.name); }
        });
    }

    // ---- Send (main composer) ----
    const composer = document.getElementById('chatComposer');
    if (composer) composer.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!body.value.trim() && (!fileInput || !fileInput.files.length)) return;
        const fd = new FormData(composer);
        try {
            const res = await fetch(composer.action, { method: 'POST', headers, body: fd });
            if (!res.ok) throw 0;
            const data = await res.json();
            appendMessage(data.html);
            stream.dataset.last = data.id;
            body.value = ''; autosize(body); fileBox.innerHTML = ''; if (fileInput) fileInput.value = '';
            toBottom();
        } catch (err) { composer.submit(); }   // fall back to full POST
    });

    function appendMessage(html) {
        if (!stream) return;
        const tmp = document.createElement('div'); tmp.innerHTML = html.trim();
        const node = tmp.firstElementChild; if (!node) return;
        if (document.getElementById(node.id)) return;   // de-dupe (poll may race)
        stream.appendChild(node);
        const empty = document.getElementById('chatEmptyStream'); if (empty) empty.remove();
    }

    // ---- Reactions / edit / delete (delegated) ----
    document.addEventListener('click', async function (e) {
        const react = e.target.closest('.chat-react, .chat-react-pick');
        if (react && stream) {
            const msg = react.closest('.chat-msg'); if (!msg) return;
            const fd = new FormData(); fd.append('emoji', react.dataset.emoji);
            try {
                const res = await fetch(msgBase + '/' + msg.dataset.id + '/react', { method: 'POST', headers, body: fd });
                if (!res.ok) throw 0;
                const d = await res.json(); replaceMessage(msg, d.html);
            } catch (err) {}
            return;
        }
        const editBtn = e.target.closest('.chat-edit-btn');
        if (editBtn) { startEdit(editBtn.closest('.chat-msg')); return; }
    });

    // Delete via the form (intercept for AJAX remove; falls back to normal POST)
    document.addEventListener('submit', async function (e) {
        const form = e.target.closest('.chat-del-form');
        if (!form) return;
        e.preventDefault();
        if (!confirm(@js(__('portal.chat.delete_confirm')))) return;
        try {
            const res = await fetch(form.action, { method: 'POST', headers, body: new FormData(form) });
            if (!res.ok) throw 0;
            const m = form.closest('.chat-msg'); if (m) m.remove();
        } catch (err) { form.submit(); }   // fall back to full POST
    });

    function replaceMessage(node, html) {
        const tmp = document.createElement('div'); tmp.innerHTML = html.trim();
        const fresh = tmp.firstElementChild; if (fresh) node.replaceWith(fresh);
    }
    function startEdit(msg) {
        if (!msg || msg.querySelector('.chat-edit-area')) return;
        const bodyEl = msg.querySelector('.chat-msg-body');
        const raw = bodyEl.dataset.raw || '';
        const area = document.createElement('div'); area.className = 'chat-edit-area';
        area.innerHTML = '<textarea class="form-control mb-1" rows="2"></textarea>' +
            '<button class="btn btn-sm btn-primary chat-edit-save">' + @js(__('portal.chat.save')) + '</button> ' +
            '<button class="btn btn-sm btn-light chat-edit-cancel">' + @js(__('portal.team.cancel')) + '</button>';
        area.querySelector('textarea').value = raw;
        bodyEl.style.display = 'none'; bodyEl.after(area);
        area.querySelector('.chat-edit-cancel').addEventListener('click', () => { area.remove(); bodyEl.style.display = ''; });
        area.querySelector('.chat-edit-save').addEventListener('click', async () => {
            const val = area.querySelector('textarea').value.trim(); if (!val) return;
            const fd = new FormData(); fd.append('_method', 'PUT'); fd.append('body', val);
            try {
                const res = await fetch(msgBase + '/' + msg.dataset.id, { method: 'POST', headers, body: fd });
                if (!res.ok) throw 0;
                const d = await res.json(); replaceMessage(msg, d.html);
            } catch (err) { alert(@js(__('portal.chat.action_failed'))); }
        });
    }

    // ---- Threads ----
    const thread = document.getElementById('chatThread');
    const threadBody = document.getElementById('chatThreadBody');
    const threadParent = document.getElementById('chatThreadParent');
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.chat-reply-btn'); if (!btn || !stream) return;
        const id = btn.dataset.id;
        try {
            const res = await fetch(stream.dataset.threadBase + '/' + id, { headers });
            const d = await res.json();
            threadBody.innerHTML = '';
            const root = document.createElement('div'); root.innerHTML = d.root; threadBody.appendChild(root.firstElementChild);
            const hr = document.createElement('hr'); threadBody.appendChild(hr);
            d.replies.forEach(r => { const t = document.createElement('div'); t.innerHTML = r.html; threadBody.appendChild(t.firstElementChild); });
            threadParent.value = id;
            thread.hidden = false;
        } catch (err) {}
    });
    const threadClose = document.getElementById('chatThreadClose');
    if (threadClose) threadClose.addEventListener('click', () => { thread.hidden = true; threadParent.value = ''; });
    const threadComposer = document.getElementById('chatThreadComposer');
    if (threadComposer) threadComposer.addEventListener('submit', async function (e) {
        e.preventDefault();
        const ta = threadComposer.querySelector('textarea'); if (!ta.value.trim()) return;
        const res = await fetch(threadComposer.action, { method: 'POST', headers, body: new FormData(threadComposer) });
        if (res.ok) {
            const d = await res.json();
            const t = document.createElement('div'); t.innerHTML = d.html; threadBody.appendChild(t.firstElementChild);
            ta.value = '';
            // bump the reply count on the root message in the main stream
            const rc = stream.querySelector('#msg-' + threadParent.value + ' .chat-reply-btn .rc');
            if (rc) { const n = parseInt(rc.dataset.count || '0', 10) + 1; rc.dataset.count = n; rc.textContent = n; }
        }
    });

    // ---- Load earlier (older messages) ----
    const loadEarlierBtn = document.getElementById('chatLoadEarlier');
    if (loadEarlierBtn) loadEarlierBtn.addEventListener('click', async function () {
        loadEarlierBtn.disabled = true;
        try {
            const res = await fetch(stream.dataset.olderUrl + '?before=' + stream.dataset.first, { headers });
            const d = await res.json();
            const prevH = stream.scrollHeight;
            const frag = document.createDocumentFragment();
            d.messages.forEach(m => {
                if (document.getElementById('msg-' + m.id)) return;
                const tmp = document.createElement('div'); tmp.innerHTML = m.html.trim();
                if (tmp.firstElementChild) frag.appendChild(tmp.firstElementChild);
            });
            loadEarlierBtn.after(frag);
            if (d.first_id) stream.dataset.first = d.first_id;
            stream.scrollTop = stream.scrollTop + (stream.scrollHeight - prevH);   // keep view anchored
            if (!d.has_more) loadEarlierBtn.hidden = true;
        } catch (e) {} finally { loadEarlierBtn.disabled = false; }
    });

    // ---- Polling ----
    async function pollMessages() {
        if (!stream) return;
        try {
            const focus = document.hasFocus() ? 1 : 0;
            const res = await fetch(stream.dataset.messagesUrl + '?after=' + stream.dataset.last + '&since=' + (stream.dataset.since || 0) + '&focus=' + focus, { headers });
            const d = await res.json();
            const stick = nearBottom();
            if (d.messages && d.messages.length) {
                d.messages.forEach(m => appendMessage(m.html));
                stream.dataset.last = d.last_id;
            }
            if (d.updated && d.updated.length) {
                d.updated.forEach(m => { const ex = document.getElementById('msg-' + m.id); if (ex) replaceMessage(ex, m.html); });
            }
            if (d.reads !== undefined) updateSeen(d.reads, d.member_count);
            if (d.since) stream.dataset.since = d.since;
            if (d.messages && d.messages.length && stick) toBottom();
        } catch (e) {}
    }
    async function pollBadges() {
        try {
            const res = await fetch(pollUrl, { headers });
            const d = await res.json();
            document.querySelectorAll('.chat-unread').forEach(el => {
                const n = (d.channels && d.channels[el.dataset.channel]) || 0;
                el.textContent = n > 9 ? '9+' : n; el.hidden = n === 0;
            });
            const navUnread = document.getElementById('navChatUnread');
            if (navUnread) { navUnread.textContent = d.total > 9 ? '9+' : d.total; navUnread.hidden = !d.total; }
            const navMent = document.getElementById('navMentionsUnread');
            if (navMent) { navMent.textContent = d.mentions > 9 ? '9+' : d.mentions; navMent.hidden = !d.mentions; }
        } catch (e) {}
    }
    pollBadges();
    if (stream) setInterval(pollMessages, 8000);
    setInterval(pollBadges, 12000);
})();
</script>
@endpush
