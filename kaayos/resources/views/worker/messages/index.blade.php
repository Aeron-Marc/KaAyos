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
                    <div class="convo-preview">{{ $convo['preview'] }}</div>
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
                <span style="font-weight:400;color:var(--g4);font-size:.82rem;margin-left:4px;">(Client)</span>
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

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

document.querySelectorAll('.convo-item').forEach(item => {
    item.addEventListener('click', function () {
        document.querySelectorAll('.convo-item').forEach(c => c.classList.remove('active'));
        this.classList.add('active');

        const convo = conversations[this.dataset.index];
        if (!convo) return;

        const pane = document.getElementById('chat-pane');

        document.getElementById('chat-empty')?.remove();

        document.getElementById('chat-header')?.remove();
        document.getElementById('chat-body')?.remove();
        document.getElementById('chat-input-row')?.remove();

        const header = document.createElement('div');
        header.className = 'chat-header';
        header.id = 'chat-header';
        header.innerHTML = '<i class="fa-regular fa-user" aria-hidden="true"></i> <span id="chat-name">' + convo.name + '</span><span style="font-weight:400;color:var(--g4);font-size:.82rem;margin-left:4px;">(Client)</span>';
        pane.prepend(header);

        const body = document.createElement('div');
        body.className = 'chat-body';
        body.id = 'chat-body';
            convo.messages.forEach(function (msg) {
                const bubble = document.createElement('div');
                bubble.className = 'chat-bubble ' + (msg.from === 'me' ? 'me' : 'them');
                bubble.innerHTML = '<div class="bubble-text">' + escapeHtml(msg.text) + '</div><div class="bubble-time">' + (msg.time || '') + '</div>';
                body.appendChild(bubble);
            });
        header.after(body);

        const inputRow = document.createElement('div');
        inputRow.className = 'chat-input-row';
        inputRow.id = 'chat-input-row';
        inputRow.innerHTML = '<input type="text" class="msg-input" placeholder="Type your message…" aria-label="Type your message"><button class="btn btn-solid send-btn" style="padding:10px 16px;"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>';
        pane.appendChild(inputRow);

        attachSendHandler(convo);

        body.scrollTop = body.scrollHeight;
    });
});

function attachSendHandler(convo) {
    const btn = document.querySelector('.send-btn');
    const input = document.querySelector('.msg-input');
    if (!btn || !input) return;

    function send() {
        const text = input.value.trim();
        if (!text) return;

        fetch('{{ route('worker.messages.send') }}', {
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
            const el = document.querySelector(`.convo-item[data-index="${i}"]`);
            if (el) el.click();
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
