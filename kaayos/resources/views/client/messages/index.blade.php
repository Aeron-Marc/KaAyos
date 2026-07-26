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
.chat-banner {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    text-align: center; font-size: .82rem; color: var(--slate);
    padding: 10px 16px; margin: 4px auto; line-height: 1.5;
    background: var(--paper-2); border-radius: 12px; max-width: 100%; width: fit-content;
}
.chat-banner.booking-status-banner {
    width: 100%;
    max-width: none;
}
.chat-banner-icon {
    flex: 0 0 auto;
    font-size: .95rem;
    line-height: 1;
}
.chat-banner-text {
    display: inline;
    text-align: center;
}

/* ── Booking status card (TikTok-style) ── */
.booking-card {
    margin: 8px auto; max-width: 320px; width: 100%;
    background: #fff; border: 1px solid var(--line);
    border-radius: 12px; padding: 16px 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
    text-align: center;
}
.bc-ref {
    font-family: 'JetBrains Mono', monospace;
    font-size: .78rem; font-weight: 600; color: var(--slate);
    margin-bottom: 2px;
}
.bc-service {
    font-weight: 700; font-size: .92rem; color: var(--graphite);
    margin-bottom: 2px;
}
.bc-date {
    font-size: .8rem; color: var(--slate); margin-bottom: 12px;
}
.bc-status {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .78rem; font-weight: 700; letter-spacing: .02em;
    padding: 4px 12px; border-radius: 20px; margin-bottom: 14px;
}
.st-new       { background: #E0ECFF; color: #1A5BBF }
.st-accepted  { background: #D8F5E4; color: #0F6E3F }
.st-en-route  { background: #FFF0D6; color: #B2600A }
.st-progress  { background: #D8EEFF; color: #0F5E8F }
.st-completed { background: #C6F0D6; color: #0A5C2F }
.st-cancelled { background: #FFE0E0; color: #A32D2D }
.bc-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--signal); color: #fff; border: none;
    border-radius: 8px; padding: 9px 18px;
    font-size: .82rem; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: background .15s;
}
.bc-btn:hover { background: var(--signal-2); }

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

@section('skeleton')
    <div class="sp-sidebar-layout">
        <div>
            <div class="skeleton skeleton-title" style="width:120px;margin-bottom:16px;"></div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;">
                <div class="skeleton skeleton-avatar-sm"></div>
                <div style="flex:1;"><div class="skeleton skeleton-text" style="width:70%;"></div><div class="skeleton skeleton-text-sm" style="width:40%;"></div></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;">
                <div class="skeleton skeleton-avatar-sm"></div>
                <div style="flex:1;"><div class="skeleton skeleton-text" style="width:60%;"></div><div class="skeleton skeleton-text-sm" style="width:50%;"></div></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;">
                <div class="skeleton skeleton-avatar-sm"></div>
                <div style="flex:1;"><div class="skeleton skeleton-text" style="width:75%;"></div><div class="skeleton skeleton-text-sm" style="width:35%;"></div></div>
            </div>
        </div>
        <div class="sp-panel">
            <div class="skeleton skeleton-title" style="width:180px;margin-bottom:20px;"></div>
            <div style="display:flex;flex-direction:column;gap:12px;padding:0 12px;">
                <div style="align-self:flex-start;max-width:70%;"><div class="skeleton" style="height:40px;border-radius:12px;"></div></div>
                <div style="align-self:flex-end;max-width:70%;"><div class="skeleton" style="height:40px;border-radius:12px;"></div></div>
                <div style="align-self:flex-start;max-width:60%;"><div class="skeleton" style="height:50px;border-radius:12px;"></div></div>
                <div style="align-self:flex-end;max-width:50%;"><div class="skeleton" style="height:36px;border-radius:12px;"></div></div>
            </div>
        </div>
    </div>
@endsection

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
                  data-conversation-id="{{ $convo['conversation_id'] }}"
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
                    @php
                        $messageText = is_string($msg['text'] ?? null) ? trim($msg['text']) : '';
                        $decodedMessage = $messageText !== '' ? json_decode($messageText, true) : null;
                        $isJsonLike = $messageText !== '' && str_starts_with($messageText, '{');
                    @endphp
                    @if(is_array($decodedMessage) && ($decodedMessage['type'] ?? null) === 'booking_status')
                        @php
                            $status = $decodedMessage['status'] ?? '';
                            $statusCopy = match ($status) {
                                'new' => 'Booking ' . ($decodedMessage['ref'] ?? '') . ' created — ' . ($decodedMessage['service'] ?? 'Booking') . ', ' . ($decodedMessage['scheduled'] ?? ''),
                                'accepted' => 'Booking ' . ($decodedMessage['ref'] ?? '') . ' accepted — worker is preparing for the job',
                                'cancelled' => 'Booking ' . ($decodedMessage['ref'] ?? '') . ' cancelled',
                                'en_route' => 'Booking ' . ($decodedMessage['ref'] ?? '') . ' is on the way',
                                'in_progress' => 'Booking ' . ($decodedMessage['ref'] ?? '') . ' is in progress',
                                'completed' => 'Booking ' . ($decodedMessage['ref'] ?? '') . ' completed',
                                default => null,
                            };
                            $statusIcon = match ($status) {
                                'new' => '📋',
                                'accepted' => '✅',
                                'cancelled' => '❌',
                                'en_route' => '🚗',
                                'in_progress' => '🔧',
                                'completed' => '✅',
                                default => 'ℹ️',
                            };
                        @endphp
                        @if($statusCopy)
                            <div class="chat-banner booking-status-banner" data-id="{{ $msg['id'] ?? '' }}">
                                <span class="chat-banner-icon" aria-hidden="true">{{ $statusIcon }}</span>
                                <span class="chat-banner-text">{{ $statusCopy }}</span>
                            </div>
                        @else
                            <div class="chat-bubble {{ $msg['from'] === 'me' ? 'me' : 'them' }}" data-id="{{ $msg['id'] ?? '' }}">
                                <div class="bubble-text">{{ $msg['text'] }}</div>
                                <div class="bubble-time">{{ $msg['time'] ?? '' }}
                                    @if($msg['from'] === 'me')
                                        @if(!empty($msg['read_at']))
                                            <span class="msg-status seen"><i class="fa-solid fa-check-double" aria-hidden="true"></i></span>
                                        @else
                                            <span class="msg-status sent"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    @elseif(($msg['is_system'] ?? false) && !$isJsonLike)
                        <div class="chat-banner" data-id="{{ $msg['id'] ?? '' }}">
                            <span class="chat-banner-text">{{ $msg['text'] }}</span>
                        </div>
                    @else
                        <div class="chat-bubble {{ $msg['from'] === 'me' ? 'me' : 'them' }}" data-id="{{ $msg['id'] ?? '' }}">
                            <div class="bubble-text">{{ $msg['text'] }}</div>
                            <div class="bubble-time">{{ $msg['time'] ?? '' }}
                                @if($msg['from'] === 'me')
                                    @if(!empty($msg['read_at']))
                                        <span class="msg-status seen"><i class="fa-solid fa-check-double" aria-hidden="true"></i></span>
                                    @else
                                        <span class="msg-status sent"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
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
const userId = {{ auth()->id() }};
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

function getBookingStatusMessage(msg) {
    if (!msg || typeof msg.text !== 'string') return null;

    var text = msg.text.trim();
    if (!text || text.charAt(0) !== '{') return null;

    var data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        return null;
    }

    if (!data || data.type !== 'booking_status') return null;

    var messages = {
        'new':         'Booking ' + (data.ref || '') + ' created — ' + (data.service || 'Booking') + ', ' + (data.scheduled || ''),
        'accepted':    'Booking ' + (data.ref || '') + ' accepted — worker is preparing for the job',
        'cancelled':   'Booking ' + (data.ref || '') + ' cancelled',
        'en_route':    'Booking ' + (data.ref || '') + ' is on the way',
        'in_progress': 'Booking ' + (data.ref || '') + ' is in progress',
        'completed':   'Booking ' + (data.ref || '') + ' completed',
    };

    var icons = {
        'new': '📋',
        'accepted': '✅',
        'cancelled': '❌',
        'en_route': '🚗',
        'in_progress': '🔧',
        'completed': '✅',
    };

    if (!messages[data.status]) return null;

    return {
        icon: icons[data.status] || 'ℹ️',
        text: messages[data.status],
    };
}

function renderBookingStatusCard(msg) {
    var bookingStatus = getBookingStatusMessage(msg);
    if (!bookingStatus) return null;

    var card = document.createElement('div');
    card.className = 'chat-banner booking-status-banner';
    card.setAttribute('data-id', msg.id);

    var icon = document.createElement('span');
    icon.className = 'chat-banner-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = bookingStatus.icon;

    var text = document.createElement('span');
    text.className = 'chat-banner-text';
    text.textContent = bookingStatus.text;

    card.appendChild(icon);
    card.appendChild(text);
    return card;
}

function renderSystemMessage(msg) {
    var card = renderBookingStatusCard(msg);
    if (card) return card;

    var isJsonLike = msg && typeof msg.text === 'string' && msg.text.trim().charAt(0) === '{';
    if (msg && msg.is_system && !isJsonLike) {
        var banner = document.createElement('div');
        banner.className = 'chat-banner';
        banner.setAttribute('data-id', msg.id);
        banner.textContent = msg.text;
        return banner;
    }

    return null;
}

function appendMessage(body, msg) {
    var rendered = renderSystemMessage(msg);
    if (rendered) {
        body.appendChild(rendered);
        return;
    }
    var bubble = document.createElement('div');
    bubble.className = 'chat-bubble ' + (msg.from === 'me' ? 'me' : 'them');
    bubble.setAttribute('data-id', msg.id);
    var statusHtml = '';
    if (msg.from === 'me') {
        if (msg.status === 'sending') {
            statusHtml = '<span class="msg-status sending"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i></span>';
        } else if (msg.read_at) {
            statusHtml = '<span class="msg-status seen"><i class="fa-solid fa-check-double" aria-hidden="true"></i></span>';
        } else {
            statusHtml = '<span class="msg-status sent"><i class="fa-solid fa-check" aria-hidden="true"></i></span>';
        }
    }
    bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(msg.text) + '</div><div class="bubble-time">' + (msg.time || '') + statusHtml + '</div>';
    body.appendChild(bubble);
}

function updateSidebarPreview(conversationId, text, time) {
    var item = document.querySelector('.convo-item[data-conversation-id="' + conversationId + '"]');
    if (!item) return;
    var preview = item.querySelector('.convo-preview');
    if (preview) preview.textContent = text.substring(0, 60);
    var timeEl = item.querySelector('.convo-time');
    if (timeEl) timeEl.textContent = time || 'just now';
    var list = item.parentNode;
    if (list) list.prepend(item);
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
            appendMessage(body, { id: e.id, text: e.text, from: e.is_system ? 'system' : 'them', is_system: e.is_system, time: e.time || 'just now' });
            if (body.scrollTop + body.clientHeight >= body.scrollHeight - 60) {
                body.scrollTop = body.scrollHeight;
            }
        }
    });
}

function startPolling(conversationId) {
    if (_pollInterval) clearInterval(_pollInterval);
    _pollInterval = setInterval(function () {
        if (!conversationId) return;
        var lastMsg = document.querySelector('#chat-body > [data-id]:last-child');
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
                if (existingIds.includes(String(msg.id))) {
                    if (msg.from === 'me' && msg.read_at) {
                        var el = body.querySelector('[data-id="' + msg.id + '"]');
                        if (el) {
                            var s = el.querySelector('.msg-status');
                            if (s) { s.className = 'msg-status seen'; s.innerHTML = '<i class="fa-solid fa-check-double" aria-hidden="true"></i>'; }
                        }
                    }
                    return;
                }
                appendMessage(body, msg);
                updateSidebarPreview(conversationId, msg.text, msg.time);
                for (var ci = 0; ci < conversations.length; ci++) {
                    if (conversations[ci].conversation_id == conversationId) {
                        conversations[ci].messages.push(msg);
                        break;
                    }
                }
            });
            if (body.scrollTop + body.clientHeight >= body.scrollHeight - 60) {
                body.scrollTop = body.scrollHeight;
            }
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
        appendMessage(body, msg);
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
    var isSending = false;

    function send() {
        if (isSending) return;
        var text = input.value.trim();
        if (!text) return;
        isSending = true;
        btn.disabled = true;

        var tempId = 'temp-' + Date.now();
        var body = document.getElementById('chat-body');

        var bubble = document.createElement('div');
        bubble.className = 'chat-bubble me';
        bubble.setAttribute('data-id', tempId);
        bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(text) + '</div><div class="bubble-time">just now<span class="msg-status sending"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i></span></div>';
        body.appendChild(bubble);
        body.scrollTop = body.scrollHeight;
        input.value = '';

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
            if (!data.success) {
                var el = body.querySelector('[data-id="' + tempId + '"]');
                if (el) {
                    var s = el.querySelector('.msg-status');
                    if (s) { s.className = 'msg-status failed'; s.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>'; }
                }
                return;
            }
            var el = body.querySelector('[data-id="' + tempId + '"]');
            if (el) {
                el.setAttribute('data-id', data.message.id);
                var s = el.querySelector('.msg-status');
                if (s) { s.className = 'msg-status sent'; s.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i>'; }
            } else {
                var existing = body.querySelector('[data-id="' + data.message.id + '"]');
                if (!existing) {
                    appendMessage(body, data.message);
                }
            }
            convo.messages.push(data.message);
            updateSidebarPreview(convo.conversation_id, data.message.text, data.message.time);
        })
        .catch(function () {
            var el = body.querySelector('[data-id="' + tempId + '"]');
            if (el) {
                var s = el.querySelector('.msg-status');
                if (s) { s.className = 'msg-status failed'; s.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>'; }
            }
        })
        .finally(function () {
            isSending = false;
            btn.disabled = false;
        });
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
    var urlParams = new URLSearchParams(window.location.search);
    var conversationParam = urlParams.get('conversation');
    if (conversationParam) {
        selectConvoByConversationId(conversationParam);
    } else {
        var active = document.querySelector('.convo-item.active');
        if (active) {
            var convo = conversations[active.dataset.index];
            if (convo) {
                activeConversationId = convo.conversation_id;
                activeConvo = convo;
                startPolling(convo.conversation_id);
                attachSendHandler(convo);
                var chatBody = document.getElementById('chat-body');
                if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
            }
        }
    }
    var checkEcho = setInterval(function () {
        if (window.Echo) {
            clearInterval(checkEcho);
            if (activeConversationId) {
                subscribeToConversation(activeConversationId);
            }
            window.Echo.private('user.' + userId)
                .listen('MessageSent', function (e) {
                    updateSidebarPreview(e.conversation_id, e.text, e.time);
                });
        }
    }, 200);
});
</script>
@endpush
