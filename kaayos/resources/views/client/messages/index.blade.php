@extends('layouts.client')

@section('title', 'Messages')
@section('page_title', 'Messages')

@section('content')

@php
    $activeConvo = collect($conversations)->firstWhere('active', true) ?? $conversations[0] ?? null;
@endphp

<div class="messages-layout">
    <div class="convo-list">
        @foreach($conversations as $convo)
            <div class="convo-item {{ !empty($convo['active']) ? 'active' : '' }}">
                <div class="convo-avatar">{{ $convo['initials'] }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="convo-name">{{ $convo['name'] }}</span>
                        <span class="convo-time">{{ $convo['time'] }}</span>
                    </div>
                    <div class="convo-preview">{{ $convo['preview'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="chat-pane">
        @if($activeConvo)
            <div class="chat-header">
                <i class="fa-solid fa-circle" style="color:#22c55e;font-size:.5rem;margin-right:6px;" aria-hidden="true"></i>
                {{ $activeConvo['name'] }}
            </div>
            <div class="chat-body">
                @foreach($activeConvo['messages'] as $msg)
                    <div class="chat-bubble {{ $msg['from'] === 'me' ? 'me' : 'them' }}">
                        {{ $msg['text'] }}
                    </div>
                @endforeach
            </div>
            <div class="chat-input-row">
                <input type="text" placeholder="Type a message…" aria-label="Message">
                <button type="button" class="btn btn-solid">
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
