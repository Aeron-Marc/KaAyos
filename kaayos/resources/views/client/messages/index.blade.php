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
                    <div class="chat-bubble {{ $msg['from'] === 'me' ? 'me' : 'them' }}">
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

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function switchConversation(index) {
    const convo = conversations[index];
    if (!convo) return;

    document.querySelectorAll('.convo-item').forEach(el => el.classList.remove('active'));
    document.querySelector(`.convo-item[data-index="${index}"]`)?.classList.add('active');

    const pane = document.getElementById('chat-pane');

    const header = document.createElement('div');
    header.className = 'chat-header';
    header.id = 'chat-header';
    var workerUrl = '/client/workers/' + (convo.worker_id || '');
    header.innerHTML = '<i class="fa-solid fa-circle" style="color:#22c55e;font-size:.5rem;margin-right:6px;" aria-hidden="true"></i> <span id="chat-worker-name">' + convo.name + '</span> <a href="' + workerUrl + '" class="chat-header-book" title="Book this worker"><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Book</a>';

    const body = document.createElement('div');
    body.className = 'chat-body';
    body.id = 'chat-body';
    convo.messages.forEach(function (msg) {
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (msg.from === 'me' ? 'me' : 'them');
        bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(msg.text) + '</div><div class="bubble-time">' + (msg.time || '') + '</div>';
        body.appendChild(bubble);
    });

    const inputRow = document.createElement('div');
    inputRow.className = 'chat-input-row';
    inputRow.id = 'chat-input-row';
    inputRow.innerHTML = '<input type="text" class="msg-input" placeholder="Type a message…" aria-label="Message"><button type="button" class="btn btn-solid send-btn"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>';

    pane.innerHTML = '';
    pane.appendChild(header);
    pane.appendChild(body);
    pane.appendChild(inputRow);

    attachSendHandler(convo);
    body.scrollTop = body.scrollHeight;
}

function attachSendHandler(convo) {
    const btn = document.querySelector('.send-btn');
    const input = document.querySelector('.msg-input');
    if (!btn || !input) return;

    function send() {
        const text = input.value.trim();
        if (!text) return;

        fetch('{{ route('client.messages.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ booking_id: convo.booking_id, message: text }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const body = document.getElementById('chat-body');
            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble me';
            bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(data.message.text) + '</div><div class="bubble-time">' + (data.message.time || 'just now') + '</div>';
            body.appendChild(bubble);
            input.value = '';
            body.scrollTop = body.scrollHeight;
        })
        .catch(() => {});
    }

    btn.addEventListener('click', send);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') send();
    });
}

function selectConvoByBookingId(bookingId) {
    for (let i = 0; i < conversations.length; i++) {
        if (String(conversations[i].booking_id) === String(bookingId)) {
            switchConversation(i);
            return;
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingParam = urlParams.get('booking');
    if (bookingParam) {
        selectConvoByBookingId(bookingParam);
    }

    const active = document.querySelector('.convo-item.active');
    if (active) {
        const convo = conversations[active.dataset.index];
        if (convo) attachSendHandler(convo);
    }
});
</script>
@endpush
