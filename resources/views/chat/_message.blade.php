@php
    use Illuminate\Support\Str;
    $me = auth()->id();
    $isMine = $m->user_id === $me;
    $canDelete = $isMine || auth()->user()->isManager();
    // Safe body: escape first, then highlight the exact mentioned users, linkify
    // URLs, and preserve line breaks. Never echo the raw body unescaped.
    $body = e($m->body);
    // Longest names first so "@Ali" doesn't break the span inside "@Ali Hassan".
    foreach ($m->mentions->sortByDesc(fn ($x) => mb_strlen((string) ($x->user?->name ?? ''))) as $mention) {
        $nm = $mention->user?->name;
        if ($nm) {
            $body = str_replace('@'.e($nm), '<span class="chat-mention">@'.e($nm).'</span>', $body);
        }
    }
    $body = preg_replace('~(https?://[^\s<]+)~', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $body);
    $body = nl2br($body);
    $reactions = $m->reactions->groupBy('emoji');
    $initials = Str::of($m->author?->name ?? '?')->explode(' ')->filter()->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
@endphp
<div class="chat-msg" id="msg-{{ $m->id }}" data-id="{{ $m->id }}">
    <div class="chat-msg-avatar" aria-hidden="true">{{ $initials ?: '?' }}</div>
    <div class="chat-msg-main">
        <div class="chat-msg-head">
            <strong>{{ $m->author?->name ?? __('portal.chat.unknown_user') }}</strong>
            <span class="chat-msg-time" title="{{ $m->created_at }}">{{ $m->created_at?->diffForHumans() }}</span>
            @if($m->wasEdited())<span class="chat-msg-edited">· {{ __('portal.chat.edited') }}</span>@endif
        </div>
        <div class="chat-msg-body" data-raw="{{ $m->body }}">{!! $body !!}</div>
        @if($m->attachments->isNotEmpty())
        <div class="chat-att">
            @foreach($m->attachments as $att)
                @if($att->isImage())
                    <a href="{{ $att->url() }}" target="_blank" class="chat-att-img"><img src="{{ $att->url() }}" alt="{{ $att->original_name }}" loading="lazy"></a>
                @else
                    <a href="{{ $att->url() }}" target="_blank" class="chat-att-file"><i class="fas fa-paperclip"></i> {{ $att->original_name }} <small class="text-muted">({{ $att->humanSize() }})</small></a>
                @endif
            @endforeach
        </div>
        @endif
        <div class="chat-msg-foot">
            @foreach($reactions as $emoji => $rs)
                <button type="button" class="chat-react {{ $rs->contains('user_id', $me) ? 'on' : '' }}" data-emoji="{{ $emoji }}"
                        title="{{ $rs->map(fn ($r) => $r->user?->name)->filter()->implode(', ') }}">{{ $emoji }} <span>{{ $rs->count() }}</span></button>
            @endforeach
            <span class="chat-react-add" tabindex="0" role="button" title="{{ __('portal.chat.add_reaction') }}"><i class="far fa-face-smile"></i>
                <span class="chat-emoji-pop">
                    @foreach($emojis as $e)<button type="button" class="chat-react-pick" data-emoji="{{ $e }}">{{ $e }}</button>@endforeach
                </span>
            </span>
            @unless($m->isReply())
                <button type="button" class="chat-reply-btn" data-id="{{ $m->id }}"><i class="far fa-comment"></i>
                    <span class="rc" data-count="{{ $m->replies_count ?? 0 }}">@if(($m->replies_count ?? 0) > 0){{ $m->replies_count }}@else{{ __('portal.chat.reply') }}@endif</span></button>
            @endunless
            @if($isMine)<button type="button" class="chat-edit-btn" data-id="{{ $m->id }}" title="{{ __('portal.chat.edit') }}"><i class="fas fa-pen"></i></button>@endif
            @if($canDelete)
            <form method="POST" action="{{ route('chat.messages.destroy', $m) }}" class="chat-del-form" onsubmit="return confirm(@js(__('portal.chat.delete_confirm')))">@csrf @method('DELETE')
                <button type="submit" class="chat-del-btn" title="{{ __('portal.chat.delete') }}"><i class="fas fa-trash"></i></button>
            </form>
            @endif
        </div>
    </div>
</div>
