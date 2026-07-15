@extends('layouts.client')

@section('title', 'Messages')
@section('page_title', 'Messages')

@push('styles')
<style>
.convo-book-top {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 16px; margin: 0 0 8px;
    background: var(--b6); color: #fff; border-radius: 8px;
    font-weight: 600; font-size: .88rem; text-decoration: none;
    transition: opacity .15s;
}
.convo-book-top:hover { opacity: .9; color: #fff; }
.chat-header-book {
    margin-left: auto;
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .8rem; font-weight: 500;
    color: var(--b6); text-decoration: none;
    padding: 4px 10px; border-radius: 6px;
    background: var(--b0); white-space: nowrap;
}
.chat-header-book:hover { background: var(--b2); color: var(--b7); }
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

@php
    $activeConvo = collect($conversations)->firstWhere('active', true) ?? $conversations[0] ?? null;
@endphp

<div class="messages-layout">
    <div class="convo-list">
        <a href="{{ route('client.workers') }}" class="convo-book-top">
            <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Book a Worker
        </a>
        @forelse($conversations as $ci => $convo)
            <div class="convo-item {{ !empty($convo['active']) ? 'active' : '' }}"
                 data-index="{{ $ci }}"
                 onclick="switchConversation({{ $ci }})">
                <div class="convo-avatar">{{ $convo['initials'] }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="convo-name">{{ $convo['name'] }}</span>
                        <span class="convo-time">{{ $convo['time'] }}</span>
                    </div>
                    <div class="convo-preview">{{ \Str::limit($convo['preview'], 60) }}</div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="padding:32px 16px;">
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <h3>No messages</h3>
                <p>Your conversations with workers will appear here.</p>
            </div>
        @endforelse
    </div>

    <div class="chat-pane" id="chat-pane">
        @if($activeConvo)
            <div class="chat-header" id="chat-header">
                <i class="fa-solid fa-circle" style="color:#22c55e;font-size:.5rem;margin-right:6px;" aria-hidden="true"></i>
                <span id="chat-worker-name">{{ $activeConvo['name'] }}</span>
                <a href="{{ route('client.workers.show', $activeConvo['worker_id']) }}" class="chat-header-book" title="Book this worker">
                    <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Book
                </a>
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
                <input type="text" class="msg-input" placeholder="Type a message…" aria-label="Message">
                <button type="button" class="btn btn-solid send-btn">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                </button>
            </div>
        @else
            <div class="empty-state" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <h3>Select a conversation</h3>
                <p>Choose a worker from the list to start chatting.</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
const conversations = @json($conversations);
let activeConversationId = null;
let activeConvo = null;
let _pollInterval = null;
let _echoChannel = null;

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function subscribeToConversation(conversationId) {
    if (_echoChannel) _echoChannel.stopListening('MessageSent');
    if (!conversationId || !window.Echo) return;
    _echoChannel = window.Echo.private('conversation.' + conversationId);
    _echoChannel.listen('MessageSent', function (e) {
        if (activeConversationId && String(e.conversation_id) === String(activeConversationId)) {
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

function startPolling(conversationId) {
    if (_pollInterval) clearInterval(_pollInterval);
    _pollInterval = setInterval(function () {
        if (!conversationId) return;
        var lastMsg = document.querySelector('#chat-body .chat-bubble:last-child');
        var afterParam = lastMsg ? '?after=' + lastMsg.getAttribute('data-id') : '';
        fetch('/client/messages/poll/' + conversationId + afterParam, {
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

function markConversationRead(conversationId) {
    fetch('/client/messages/' + conversationId + '/read', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).catch(function () {});
}

function switchConversation(index) {
    var convo = conversations[index];
    if (!convo) return;

    activeConversationId = convo.conversation_id;
    activeConvo = convo;
    subscribeToConversation(convo.conversation_id);
    markConversationRead(convo.conversation_id);

    document.querySelectorAll('.convo-item').forEach(function (el) { el.classList.remove('active'); });
    var target = document.querySelector('.convo-item[data-index="' + index + '"]');
    if (target) target.classList.add('active');

    var pane = document.getElementById('chat-pane');
    var workerUrl = '/client/workers/' + (convo.worker_id || '');

    var header = document.createElement('div');
    header.className = 'chat-header';
    header.id = 'chat-header';
    header.innerHTML = '<i class="fa-solid fa-circle" style="color:#22c55e;font-size:.5rem;margin-right:6px;" aria-hidden="true"></i> <span id="chat-worker-name">' + convo.name + '</span> <a href="' + workerUrl + '" class="chat-header-book" title="Book this worker"><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Book</a>';

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

    var inputRow = document.createElement('div');
    inputRow.className = 'chat-input-row';
    inputRow.id = 'chat-input-row';
    inputRow.innerHTML = '<input type="text" class="msg-input" placeholder="Type a message…" aria-label="Message"><button type="button" class="btn btn-solid send-btn"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>';

    pane.innerHTML = '';
    pane.appendChild(header);
    pane.appendChild(body);
    pane.appendChild(inputRow);

    attachSendHandler(convo);
    startPolling(convo.conversation_id);
    body.scrollTop = body.scrollHeight;
}

function attachSendHandler(convo) {
    var btn = document.querySelector('.send-btn');
    var input = document.querySelector('.msg-input');
    if (!btn || !input) return;

    function send() {
        var text = input.value.trim();
        if (!text) return;

        fetch('{{ route('client.messages.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Socket-ID': window.Echo ? window.Echo.socketId() : '',
            },
            body: JSON.stringify({ conversation_id: convo.conversation_id, message: text }),
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

function selectConvoByConversationId(conversationId) {
    for (var i = 0; i < conversations.length; i++) {
        if (String(conversations[i].conversation_id) === String(conversationId)) {
            switchConversation(i);
            return;
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var checkEcho = setInterval(function () {
        if (window.Echo) {
            clearInterval(checkEcho);

            var urlParams = new URLSearchParams(window.location.search);
            var conversationParam = urlParams.get('conversation');
            if (conversationParam) {
                selectConvoByConversationId(conversationParam);
                return;
            }

            var active = document.querySelector('.convo-item.active');
            if (active) {
                var convo = conversations[active.dataset.index];
                if (convo) {
                    activeConversationId = convo.conversation_id;
                    activeConvo = convo;
                    subscribeToConversation(convo.conversation_id);
                    startPolling(convo.conversation_id);
                    attachSendHandler(convo);
                }
            }
        }
    }, 200);
});
</script>
@endpush
