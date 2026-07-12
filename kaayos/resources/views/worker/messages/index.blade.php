@extends('layouts.worker')

@section('title', 'Messages')
@section('page_title', 'Messages')

@push('styles')
<style>
.unread-badge {
    background: var(--b6); color: #fff; border-radius: 100px;
    padding: 1px 7px; font-size: .7rem; font-weight: 700;
}
.convo-item { cursor: pointer; }
.bubble-text { line-height: 1.5; }
.bubble-time { font-size: .65rem; opacity: .6; margin-top: 4px; }
.chat-bubble.me .bubble-time { text-align: right; }

/* ── Chat viewport fix (desktop) ── */
@media (min-width: 769px) {
    .content { overflow: hidden; padding: 0; }
    .messages-layout {
        height: calc(100vh - 64px);
        min-height: 0;
        border-radius: 0;
        grid-template-rows: minmax(0, 1fr);
    }
    .messages-layout > * { min-height: 0; }
}
</style>
@endpush

@section('content')

<div class="messages-layout">
    <div class="convo-list">
        @forelse($conversations as $i => $convo)
            <div class="convo-item {{ $convo['active'] ? 'active' : '' }}" data-index="{{ $i }}">
                <div class="convo-avatar">{{ $convo['initials'] }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="convo-name">{{ $convo['name'] }}</span>
                        <span style="display:flex;align-items:center;gap:6px;">
                            @if(($convo['unread_count'] ?? 0) > 0)
                                <span class="unread-badge">{{ $convo['unread_count'] }}</span>
                            @endif
                            <span class="convo-time">{{ $convo['time'] }}</span>
                        </span>
                    </div>
                    <div class="convo-preview">{{ $convo['service'] ?? '' }} - {{ $convo['preview'] }}</div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <h3>No messages yet</h3>
                <p>When a client reaches out, conversations will appear here.</p>
            </div>
        @endforelse
    </div>

    <div class="chat-pane" id="chat-pane">
        @php $activeConvo = collect($conversations)->firstWhere('active', true); @endphp
        @if($activeConvo)
            <div class="chat-header" id="chat-header">
                <i class="fa-regular fa-user" aria-hidden="true"></i>
                <span id="chat-name">{{ $activeConvo['name'] }}</span>
                <span style="font-weight:400;color:var(--g4);font-size:.82rem;margin-left:4px;">({{ $activeConvo['service'] ?? 'Client' }})</span>
            </div>
            <div class="chat-body" id="chat-body">
                @foreach($activeConvo['messages'] as $msg)
                    <div class="chat-bubble {{ $msg['from'] === 'me' ? 'me' : 'them' }}" data-id="{{ $msg['id'] ?? '' }}">
                        <div class="bubble-text">{{ $msg['text'] }}</div>
                        <div class="bubble-time">{{ $msg['time'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
            <div class="chat-input-row" id="chat-input-row">
                <input type="text" class="msg-input" placeholder="Type your message…" aria-label="Type your message">
                <button class="btn btn-solid send-btn" style="padding:10px 16px;">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                </button>
            </div>
        @else
            <div id="chat-empty" style="display:flex;align-items:center;justify-content:center;flex:1;padding:40px;">
                <div class="empty-state">
                    <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                    <h3>Select a conversation</h3>
                    <p>Choose a client from the left to start chatting.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
const conversations = @json($conversations);
let activeBookingId = null;
let activeConvo = null;
let _pollInterval = null;
let _echoChannel = null;

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function subscribeToBooking(bookingId) {
    if (_echoChannel) _echoChannel.stopListening('MessageSent');
    if (!bookingId || !window.Echo) return;
    _echoChannel = window.Echo.private('booking.' + bookingId);
    _echoChannel.listen('MessageSent', function (e) {
        if (activeBookingId && String(e.booking_id) === String(activeBookingId)) {
            var body = document.getElementById('chat-body');
            if (!body) return;
            if (body.querySelector('[data-id="' + e.id + '"]')) return;
            var bubble = document.createElement('div');
            bubble.className = 'chat-bubble them';
            bubble.setAttribute('data-id', e.id);
            bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(e.text) + '</div><div class="bubble-time">' + (e.time || 'just now') + '</div>';
            body.appendChild(bubble);
            body.scrollTop = body.scrollHeight;
        }
    });
}

function startPolling(bookingId) {
    if (_pollInterval) clearInterval(_pollInterval);
    _pollInterval = setInterval(function () {
        if (!bookingId) return;
        var lastMsg = document.querySelector('#chat-body .chat-bubble:last-child');
        var afterParam = lastMsg ? '?after=' + lastMsg.getAttribute('data-id') : '';
        fetch('/worker/messages/poll/' + bookingId + afterParam, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.messages || !activeConvo) return;
            var body = document.getElementById('chat-body');
            if (!body) return;
            var existingIds = Array.from(body.querySelectorAll('[data-id]')).map(function (el) { return el.getAttribute('data-id'); });
            data.messages.forEach(function (msg) {
                if (msg.from === 'them' && !existingIds.includes(String(msg.id))) {
                    var bubble = document.createElement('div');
                    bubble.className = 'chat-bubble them';
                    bubble.setAttribute('data-id', msg.id);
                    bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(msg.text) + '</div><div class="bubble-time">' + (msg.time || '') + '</div>';
                    body.appendChild(bubble);
                }
            });
            body.scrollTop = body.scrollHeight;
        })
        .catch(function () {});
    }, 2000);
}

function markConversationRead(bookingId) {
    fetch('/worker/messages/' + bookingId + '/read', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).catch(function () {});
}

document.querySelectorAll('.convo-item').forEach(function (item) {
    item.addEventListener('click', function () {
        document.querySelectorAll('.convo-item').forEach(function (c) { c.classList.remove('active'); });
        this.classList.add('active');

        var convo = conversations[this.dataset.index];
        if (!convo) return;

        activeBookingId = convo.booking_id;
        activeConvo = convo;
        subscribeToBooking(convo.booking_id);
        markConversationRead(convo.booking_id);

        var pane = document.getElementById('chat-pane');

        var emptyEl = document.getElementById('chat-empty');
        if (emptyEl) emptyEl.remove();

        var oldHeader = document.getElementById('chat-header');
        if (oldHeader) oldHeader.remove();
        var oldBody = document.getElementById('chat-body');
        if (oldBody) oldBody.remove();
        var oldInput = document.getElementById('chat-input-row');
        if (oldInput) oldInput.remove();

        var header = document.createElement('div');
        header.className = 'chat-header';
        header.id = 'chat-header';
        header.innerHTML = '<i class="fa-regular fa-user" aria-hidden="true"></i> <span id="chat-name">' + convo.name + '</span><span style="font-weight:400;color:var(--g4);font-size:.82rem;margin-left:4px;">(' + (convo.service || 'Client') + ')</span>';
        pane.prepend(header);

        var body = document.createElement('div');
        body.className = 'chat-body';
        body.id = 'chat-body';
        convo.messages.forEach(function (msg) {
            var bubble = document.createElement('div');
            bubble.className = 'chat-bubble ' + (msg.from === 'me' ? 'me' : 'them');
            bubble.setAttribute('data-id', msg.id);
            bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(msg.text) + '</div><div class="bubble-time">' + (msg.time || '') + '</div>';
            body.appendChild(bubble);
        });
        header.after(body);

        var inputRow = document.createElement('div');
        inputRow.className = 'chat-input-row';
        inputRow.id = 'chat-input-row';
        inputRow.innerHTML = '<input type="text" class="msg-input" placeholder="Type your message…" aria-label="Type your message"><button class="btn btn-solid send-btn" style="padding:10px 16px;"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>';
        pane.appendChild(inputRow);

        attachSendHandler(convo);
        startPolling(convo.booking_id);
        body.scrollTop = body.scrollHeight;
    });
});

function attachSendHandler(convo) {
    var btn = document.querySelector('.send-btn');
    var input = document.querySelector('.msg-input');
    if (!btn || !input) return;

    function send() {
        var text = input.value.trim();
        if (!text) return;

        fetch('{{ route('worker.messages.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Socket-ID': window.Echo ? window.Echo.socketId() : '',
            },
            body: JSON.stringify({ booking_id: convo.booking_id, message: text }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) return;
            var body = document.getElementById('chat-body');
            if (body.querySelector('[data-id="' + data.message.id + '"]')) return;
            var bubble = document.createElement('div');
            bubble.className = 'chat-bubble me';
            bubble.setAttribute('data-id', data.message.id || '');
            bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(data.message.text) + '</div><div class="bubble-time">' + (data.message.time || 'just now') + '</div>';
            body.appendChild(bubble);
            input.value = '';
            body.scrollTop = body.scrollHeight;
        })
        .catch(function () {});
    }

    btn.addEventListener('click', send);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') send();
    });
}

function selectConvoByBookingId(bookingId) {
    for (var i = 0; i < conversations.length; i++) {
        if (String(conversations[i].booking_id) === String(bookingId)) {
            var el = document.querySelector('.convo-item[data-index="' + i + '"]');
            if (el) el.click();
            return;
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var checkEcho = setInterval(function () {
        if (window.Echo) {
            clearInterval(checkEcho);

            var urlParams = new URLSearchParams(window.location.search);
            var bookingParam = urlParams.get('booking');
            if (bookingParam) {
                selectConvoByBookingId(bookingParam);
                return;
            }

            var active = document.querySelector('.convo-item.active');
            if (active) {
                var convo = conversations[active.dataset.index];
                if (convo) {
                    activeBookingId = convo.booking_id;
                    activeConvo = convo;
                    subscribeToBooking(convo.booking_id);
                    startPolling(convo.booking_id);
                    attachSendHandler(convo);
                }
            }
        }
    }, 200);
});
</script>
@endpush
