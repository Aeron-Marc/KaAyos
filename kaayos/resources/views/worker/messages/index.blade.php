@extends('layouts.worker')

@section('title', 'Messages')
@section('page_title', 'Messages')

@section('content')

<div class="messages-layout">
    <div class="convo-list">
        @forelse($conversations as $convo)
            <div class="convo-item {{ $convo['active'] ? 'active' : '' }}">
                <div class="convo-avatar">{{ $convo['initials'] }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="convo-name">{{ $convo['name'] }}</span>
                        <span class="convo-time">{{ $convo['time'] }}</span>
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

    <div class="chat-pane">
        @php $activeConvo = collect($conversations)->firstWhere('active', true); @endphp
        @if($activeConvo)
            <div class="chat-header">
                <i class="fa-regular fa-user" aria-hidden="true"></i>
                {{ $activeConvo['name'] }}
                <span style="font-weight:400;color:var(--g4);font-size:.82rem;margin-left:4px;">(Client)</span>
            </div>
            <div class="chat-body">
                @foreach($activeConvo['messages'] as $msg)
                    <div class="chat-bubble {{ $msg['from'] === 'me' ? 'me' : 'them' }}">
                        {{ $msg['text'] }}
                    </div>
                @endforeach
            </div>
            <div class="chat-input-row">
                <input type="text" placeholder="Type your message…" aria-label="Type your message">
                <button class="btn btn-solid" style="padding:10px 16px;">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                </button>
            </div>
        @else
            <div style="display:flex;align-items:center;justify-content:center;flex:1;padding:40px;">
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
