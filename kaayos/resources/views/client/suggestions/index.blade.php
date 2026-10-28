@extends('layouts.client')

@section('title', 'Suggestions')
@section('page_title')
    Suggestions <span class="suggestions-ai-badge">AI</span>
@endsection

@section('skeleton')
    <div style="margin:-28px;height:calc(100vh - var(--topbar-h));display:flex;flex-direction:column;gap:16px;padding:28px;background:#f8fafc;">
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="align-self:flex-start;max-width:60%;">
                <div class="skeleton" style="height:48px;border-radius:12px 12px 12px 4px;"></div>
            </div>
            <div style="align-self:flex-end;max-width:40%;">
                <div class="skeleton" style="height:36px;border-radius:12px 12px 4px 12px;"></div>
            </div>
            <div style="align-self:flex-start;max-width:50%;">
                <div class="skeleton" style="height:52px;border-radius:12px 12px 12px 4px;"></div>
            </div>
            <div style="align-self:flex-end;max-width:35%;">
                <div class="skeleton" style="height:36px;border-radius:12px 12px 4px 12px;"></div>
            </div>
        </div>
        <div class="skeleton" style="height:44px;border-radius:99px;max-width:400px;"></div>
    </div>
@endsection

@section('content')

<div class="suggestion-fullwrap">
  <section id="suggestion-chat" class="suggestion-chat"
       data-csrf="{{ csrf_token() }}">
    <div class="suggestion-chat-header">
      <div class="suggestion-chat-avatar">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
      </div>
      <div>
        <div class="suggestion-chat-title">KaAyos AI</div>
        <div class="suggestion-chat-status"><span class="ai-status-dot"></span> Online · Ready to help</div>
      </div>
    </div>
    <div id="suggestion-messages" class="suggestion-messages">
      <div class="s-msg bot">
        <div class="s-ai-avatar"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
        <div class="s-msg-bubble">
          <p>Hi! I can help you find the right worker. Tell me what you need — like <strong>"plumber for leaking pipe"</strong> or <strong>"electrician for wiring"</strong>.</p>
          <span class="s-msg-time">Just now</span>
        </div>
      </div>
    </div>
    <div id="suggestion-chips" class="suggestion-chips"></div>
    <div class="suggestion-input-bar">
      <input type="text" id="suggestion-input" class="suggestion-input"
        placeholder="Describe what you need..." maxlength="1000" autocomplete="off" />
      <button id="suggestion-send" class="suggestion-send" aria-label="Send">
        <i class="fa-solid fa-paper-plane"></i>
      </button>
    </div>
  </section>
  <aside class="suggestion-map-pane" aria-label="Recommended workers map">
    <div class="suggestion-map-toolbar">
      <div>
        <strong>Workers near you</strong>
        <span id="suggestion-map-count">Search to see matches</span>
      </div>
      <button type="button" id="suggestion-map-location" class="suggestion-map-control" aria-label="Show my current location" title="Show my current location">
        <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i>
      </button>
      <button type="button" id="suggestion-map-layer" class="suggestion-map-control" aria-label="Toggle map layer" title="Toggle map layer">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
      </button>
    </div>
    <div id="suggestion-map" class="suggestion-map"></div>
    <div id="suggestion-map-empty" class="suggestion-map-empty">
      <span class="suggestion-map-empty-icon"><i class="fa-solid fa-location-dot"></i></span>
      <strong>Find a trusted worker</strong>
      <span>Your recommendations will appear here.</span>
    </div>
  </aside>
</div>

@endsection

@push('styles')
<style>
.suggestion-fullwrap {
  margin: -28px;
  height: calc(100vh - var(--topbar-h));
  display: flex;
  flex-direction: column;
}
.suggestion-chat {
  display: flex;
  flex-direction: column;
  height: 100%;
  flex: 1;
  background: #fff;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  overflow: hidden;
  font-family: 'Inter', sans-serif;
}
.suggestion-chat-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: #1A6FC4;
  color: #fff;
  flex-shrink: 0;
}
.suggestion-chat-avatar {
  width: 40px;
  height: 40px;
  background: rgba(255,255,255,0.18);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}
.suggestion-chat-title {
  font-weight: 600;
  font-size: 0.95rem;
}
.suggestion-chat-status {
  font-size: 0.72rem;
  opacity: 0.78;
}

.suggestion-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  scroll-behavior: smooth;
  background: #f8fafc;
}
.suggestion-messages::-webkit-scrollbar { width: 4px; }
.suggestion-messages::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

.s-msg {
  display: flex;
  max-width: 85%;
  animation: sFadeIn 0.2s ease;
}
@keyframes sFadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
.s-msg.bot { align-self: flex-start; }
.s-msg.user { align-self: flex-end; }

.s-msg-bubble {
  padding: 10px 16px;
  border-radius: 14px;
  line-height: 1.6;
  font-size: 0.88rem;
  word-wrap: break-word;
  white-space: pre-wrap;
  position: relative;
}
.s-msg.bot .s-msg-bubble {
  background: #fff;
  color: #1B2430;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.s-msg.user .s-msg-bubble {
  background: #1A6FC4;
  color: #fff;
  border-bottom-right-radius: 4px;
}
.s-msg-bubble p { margin: 0; }
.s-msg-bubble a { color: inherit; text-decoration: underline; }
.s-msg-time {
  display: block;
  font-size: 0.65rem;
  opacity: 0.55;
  margin-top: 4px;
}

.typing .s-msg-bubble {
  background: #fff;
  display: flex;
  gap: 4px;
  padding: 14px 18px;
}
.typing .s-msg-bubble span {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #8C97A4;
  animation: sTyping 1.4s infinite;
}
.typing .s-msg-bubble span:nth-child(2) { animation-delay: 0.2s; }
.typing .s-msg-bubble span:nth-child(3) { animation-delay: 0.4s; }
@keyframes sTyping {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-5px); }
}

.suggestion-chips {
  padding: 8px 20px 4px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  flex-shrink: 0;
  background: #f8fafc;
}
.s-chip {
  background: #e6f1fb;
  color: #0C447C;
  border: 1px solid #b5d4f4;
  border-radius: 100px;
  padding: 5px 14px;
  font-size: 0.78rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
  font-family: inherit;
}
.s-chip:hover {
  background: #1A6FC4;
  color: #fff;
  border-color: #1A6FC4;
}

.suggestion-input-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px 16px;
  border-top: 1px solid #e8ecf0;
  flex-shrink: 0;
  background: #fff;
}
.suggestion-input {
  flex: 1;
  border: 1.5px solid #e8ecf0;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.88rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.15s;
}
.suggestion-input:focus { border-color: #1A6FC4; }
.suggestion-send {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #1A6FC4;
  color: #fff;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
  flex-shrink: 0;
}
.suggestion-send:hover { background: #185FA5; }
.suggestion-send:disabled { background: #b5d4f4; cursor: not-allowed; }

.s-worker-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin: 4px 0;
}
.s-worker-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  background: #f5f7fa;
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: background 0.15s;
}
.s-worker-card:hover {
  background: #e6f1fb;
}
.s-worker-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: #d0d9e4;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  color: #4a5a6e;
}
.s-worker-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.s-worker-initials {
  font-size: 0.75rem;
  font-weight: 600;
}
.s-worker-info {
  flex: 1;
  min-width: 0;
}
.s-worker-name {
  font-weight: 600;
  font-size: 0.82rem;
  color: #1B2430;
}
.s-worker-meta {
  font-size: 0.72rem;
  color: #6B7A8C;
  margin-top: 1px;
}
.s-worker-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 3px;
}
.s-worker-rating {
  font-size: 0.72rem;
  color: #e6a817;
}
.s-worker-rating i {
  font-size: 0.65rem;
}
.s-worker-match {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 1px 7px;
  border-radius: 100px;
}
.s-worker-match.high { background: #d4edda; color: #155724; }
.s-worker-match.mid { background: #fff3cd; color: #856404; }
.s-worker-match.low { background: #f8d7da; color: #721c24; }

.s-msg-map {
  width: 92%;
  max-width: 92%;
  margin: 0 auto;
  animation: sFadeIn 0.2s ease;
}
.s-map-wrap {
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #e0e4e8;
  position: relative;
}
.s-map-expand-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 500;
  width: 30px;
  height: 30px;
  background: rgba(255,255,255,0.92);
  border: 1px solid #d0d9e4;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #4a5a6e;
  font-size: 0.9rem;
  transition: background 0.15s, color 0.15s;
  padding: 0;
}
.s-map-expand-btn:hover {
  background: #fff;
  color: #1A6FC4;
}
.s-map {
  height: 260px;
  width: 100%;
  display: block;
}
.suggestion-messages.map-expanded {
  overflow: hidden;
}
.suggestion-messages.map-expanded .s-msg-map {
  width: 100% !important;
  max-width: 100% !important;
  margin: 0 !important;
  padding: 16px !important;
  box-sizing: border-box;
  animation: none;
  position: absolute;
  inset: 0;
  background: #f8fafc;
  z-index: 10;
  overflow: hidden;
}
.suggestion-messages.map-expanded .s-map-wrap {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  border: 1px solid #e0e4e8;
  overflow: hidden;
}
.suggestion-messages.map-expanded .s-map {
  height: 100%;
}
.suggestion-messages.map-expanded .s-map-expand-btn {
  top: 24px;
  right: 24px;
}
@media(max-width:768px){
  .suggestion-fullwrap {
    margin: -18px;
    height: calc(100vh - var(--topbar-h));
  }
  .suggestion-chat {
    height: 100%;
    box-shadow: none;
  }
  .s-msg{max-width:92%}
  .s-chip{font-size:.8rem;padding:6px 12px}
  .s-worker-card{padding:12px}
  .suggestion-messages.map-expanded .s-msg-map {
    padding: 10px;
  }
  .suggestion-messages.map-expanded .s-map-expand-btn {
    top: 18px;
    right: 18px;
  }
}

/* Split-pane AI workspace */
.suggestions-ai-badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 999px;
  background: #dbeafe;
  color: #2563eb;
  font-size: .72rem;
  font-weight: 700;
}
.suggestion-fullwrap {
  margin: -28px;
  height: calc(100vh - var(--topbar-h));
  display: grid;
  grid-template-columns: minmax(0, 55fr) minmax(360px, 45fr);
  gap: 14px;
  padding: 14px;
  background: #f1f5f9;
}
.suggestion-chat {
  min-width: 0;
  height: 100%;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  background: #f8fafc;
  box-shadow: 0 8px 30px rgba(15, 23, 42, .06);
  overflow: hidden;
}
.suggestion-chat-header {
  padding: 17px 20px;
  border-radius: 18px 18px 0 0;
  background: #fff;
  color: #172b4d;
  border-bottom: 1px solid #e2e8f0;
}
.suggestion-chat-avatar {
  width: 42px;
  height: 42px;
  border-radius: 13px;
  background: #2563eb;
  box-shadow: 0 5px 12px rgba(37, 99, 235, .2);
}
.suggestion-chat-title { color: #172b4d; font-size: 1rem; }
.suggestion-chat-status { color: #64748b; opacity: 1; }
.ai-status-dot {
  display: inline-block;
  width: 7px;
  height: 7px;
  margin-right: 4px;
  border-radius: 50%;
  background: #22c55e;
}
.suggestion-messages {
  padding: 20px;
  background: #f8fafc;
}
.s-msg { align-items: flex-start; gap: 8px; max-width: 88%; min-width: 0; }
.s-msg.user { flex-direction: row-reverse; }
.s-ai-avatar,
.s-user-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  flex-shrink: 0;
  border-radius: 10px;
  font-size: .72rem;
  font-weight: 700;
}
.s-ai-avatar { background: #3b82f6; color: #fff; }
.s-user-avatar { background: #dbeafe; color: #1d4ed8; }
.s-msg.bot .s-msg-bubble { border: 1px solid #f1f5f9; border-radius: 18px 18px 18px 4px; }
.s-msg.user .s-msg-bubble { background: #2563eb; border-radius: 18px 18px 4px 18px; }
.s-msg-bubble { padding: 12px 15px; min-width: 0; overflow-wrap: anywhere; }
.s-msg-bubble.typing-text::after {
  content: '';
  display: inline-block;
  width: 2px;
  height: 1em;
  margin-left: 3px;
  vertical-align: -.12em;
  background: #2563eb;
  animation: blinkCursor .8s step-end infinite;
}
@keyframes blinkCursor { 50% { opacity: 0; } }
.suggestion-chips {
  padding: 10px 20px;
  background: #f8fafc;
  overflow-x: auto;
  flex-wrap: nowrap;
}
.s-chip {
  padding: 7px 14px;
  background: #eff6ff;
  color: #1d4ed8;
  border-color: #dbeafe;
}
.s-chip:hover { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
.suggestion-input-bar { padding: 14px 18px 18px; background: #fff; border-radius: 0 0 18px 18px; }
.suggestion-input { border-radius: 999px; padding: 12px 18px; box-shadow: inset 0 1px 3px rgba(15,23,42,.04); }
.suggestion-send { width: 44px; height: 44px; background: #2563eb; transition: transform .15s, background .15s; }
.suggestion-send:hover { background: #1d4ed8; }
.suggestion-send:active { transform: scale(.95); }
.s-worker-list { gap: 8px; margin-top: 10px; }
.s-worker-card {
  position: relative;
  min-width: 0;
  padding: 12px;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 2px 7px rgba(15,23,42,.04);
  transition: box-shadow .15s, border-color .15s, transform .15s;
}
.s-worker-card:hover,
.s-worker-card.is-selected { border-color: #93c5fd; box-shadow: 0 6px 16px rgba(37,99,235,.14); transform: translateY(-1px); }
.s-worker-avatar { width: 42px; height: 42px; background: #dbeafe; color: #1d4ed8; }
.s-worker-name { color: #172b4d; }
.s-worker-match.high { background: #dbeafe; color: #1d4ed8; }
.s-worker-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  margin-left: 4px;
  border: 0;
  border-radius: 50%;
  background: #f1f5f9;
  color: #64748b;
  text-decoration: none;
}
.s-worker-action:hover { background: #eff6ff; color: #2563eb; }
.suggestion-worker-marker { background: transparent; border: 0; }
.suggestion-marker-pin {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 74px;
  height: 30px;
  border: 2px solid #fff;
  border-radius: 999px 999px 999px 4px;
  background: var(--marker-color);
  color: #fff;
  box-shadow: 0 3px 10px rgba(15,23,42,.25);
  transform: rotate(-45deg);
}
.suggestion-marker-pin::after {
  content: '';
  position: absolute;
  right: -5px;
  bottom: -6px;
  width: 10px;
  height: 10px;
  background: var(--marker-color);
  clip-path: polygon(0 0, 100% 0, 0 100%);
}
.suggestion-marker-pin span { transform: rotate(45deg); font-size: .68rem; font-weight: 700; }
.suggestion-worker-marker.is-active .suggestion-marker-pin { box-shadow: 0 0 0 8px rgba(59,130,246,.25), 0 3px 10px rgba(15,23,42,.25); }
.suggestion-popup { display: flex; flex-direction: column; gap: 3px; min-width: 150px; font-family: inherit; }
.suggestion-popup strong { color: #172b4d; font-size: .84rem; }
.suggestion-popup span { color: #64748b; font-size: .72rem; }
.suggestion-popup b { color: #2563eb; font-size: .75rem; }
.suggestion-popup a { margin-top: 3px; color: #2563eb; font-size: .74rem; font-weight: 700; text-decoration: none; }
.suggestion-map-pane {
  position: relative;
  min-width: 0;
  overflow: hidden;
  border: 1px solid #dbeafe;
  border-radius: 18px;
  background: #dbeafe;
  box-shadow: 0 8px 30px rgba(15, 23, 42, .06);
}
.suggestion-map { width: 100%; height: 100%; min-height: 420px; }
.suggestion-map-toolbar {
  position: absolute;
  top: 14px;
  left: 14px;
  right: 14px;
  z-index: 500;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border: 1px solid rgba(226,232,240,.9);
  border-radius: 12px;
  background: rgba(255,255,255,.94);
  box-shadow: 0 4px 14px rgba(15,23,42,.12);
}
.suggestion-map-toolbar strong { display: block; color: #172b4d; font-size: .82rem; }
.suggestion-map-toolbar span { display: block; margin-top: 2px; color: #64748b; font-size: .68rem; }
.suggestion-map-control {
  width: 32px;
  height: 32px;
  border: 1px solid #dbeafe;
  border-radius: 9px;
  background: #fff;
  color: #2563eb;
  cursor: pointer;
}
.suggestion-map-toolbar > div + .suggestion-map-control { margin-left: auto; }
.suggestion-map-control + .suggestion-map-control { margin-left: 6px; }
.suggestion-client-marker {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px !important;
  height: 22px !important;
  border: 3px solid #fff;
  border-radius: 50%;
  background: #2563eb;
  box-shadow: 0 0 0 8px rgba(37, 99, 235, .2), 0 2px 8px rgba(15, 23, 42, .3);
}
.suggestion-client-marker::after {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #fff;
}
.suggestion-client-location-popup strong { color: #172b4d; font-size: .78rem; }
.suggestion-client-location-popup span { color: #64748b; font-size: .68rem; }
.suggestion-map-empty {
  position: absolute;
  top: 50%;
  left: 50%;
  z-index: 400;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  width: 190px;
  color: #64748b;
  text-align: center;
  transform: translate(-50%, -50%);
  pointer-events: none;
}
.suggestion-map-empty strong { color: #334155; font-size: .88rem; }
.suggestion-map-empty span:last-child { font-size: .74rem; }
.suggestion-map-empty-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 46px;
  height: 46px;
  margin-bottom: 4px;
  border-radius: 50%;
  background: #fff;
  color: #2563eb;
  box-shadow: 0 4px 12px rgba(37,99,235,.16);
}
@media (max-width: 900px) {
  .suggestion-fullwrap { grid-template-columns: 1fr; height: auto; min-height: calc(100vh - var(--topbar-h)); }
  .suggestion-chat { min-height: 680px; }
  .suggestion-map-pane { height: 420px; }
}
@media (max-width: 640px) {
  .suggestion-fullwrap { margin: -18px; padding: 8px; gap: 8px; }
  .suggestion-chat { min-height: 650px; border-radius: 14px; }
  .suggestion-chat-header { border-radius: 14px 14px 0 0; }
  .suggestion-input-bar { border-radius: 0 0 14px 14px; }
  .suggestion-map-pane { height: 340px; border-radius: 14px; }
  .suggestion-messages { padding: 14px; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
  const CSRF = document.getElementById('suggestion-chat')?.dataset?.csrf || '';

  const el = {
    messages: document.getElementById('suggestion-messages'),
    chips: document.getElementById('suggestion-chips'),
    input: document.getElementById('suggestion-input'),
    send: document.getElementById('suggestion-send'),
  };

  let history = [];
  let suggestionMap = null;
  let suggestionMarkers = [];
  let suggestionWorkers = [];
  let mapLayer = 'street';
  let clientLocation = null;
  let clientLocationMarker = null;
  let clientLocationAccuracy = null;

  function scrollBottom() {
    setTimeout(() => { el.messages.scrollTop = el.messages.scrollHeight; }, 50);
  }

  function showTyping() {
    const d = document.createElement('div');
    d.className = 's-msg bot typing';
    d.id = 's-typing';
    d.innerHTML = '<div class="s-msg-bubble"><span></span><span></span><span></span></div>';
    el.messages.appendChild(d);
    scrollBottom();
  }

  function hideTyping() {
    const t = document.getElementById('s-typing');
    if (t) t.remove();
  }

  function addMsg(role, text) {
    const d = document.createElement('div');
    d.className = 's-msg ' + role;
    const b = document.createElement('div');
    b.className = 's-msg-bubble';
    const p = document.createElement('p');
    p.textContent = text;
    b.appendChild(p);
    const t = document.createElement('span');
    t.className = 's-msg-time';
    t.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    b.appendChild(t);
    const avatar = document.createElement('span');
    avatar.className = 's-user-avatar';
    avatar.textContent = 'JA';
    d.appendChild(avatar);
    d.appendChild(b);
    el.messages.appendChild(d);
    scrollBottom();
    history.push({ role, content: text });
  }

  function addBotReply(html, animate) {
    html = formatAssistantHtml(html);
    const d = document.createElement('div');
    d.className = 's-msg bot';
    const b = document.createElement('div');
    b.className = 's-msg-bubble';
    b.setAttribute('aria-live', 'polite');
    b.setAttribute('aria-atomic', 'true');
    const recordHistory = function () {
      history.push({ role: 'assistant', content: b.textContent || '' });
    };
    const addTime = function () {
      const t = document.createElement('span');
      t.className = 's-msg-time';
      t.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      b.appendChild(t);
    };
    if (animate !== false && !html.includes('s-worker-list')) {
      b.classList.add('typing-text');
      typeReply(b, html, function () {
        addTime();
        recordHistory();
      });
    } else {
      b.innerHTML = html;
      addTime();
      recordHistory();
    }
    const avatar = document.createElement('span');
    avatar.className = 's-ai-avatar';
    avatar.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>';
    d.appendChild(avatar);
    d.appendChild(b);
    el.messages.appendChild(d);
    scrollBottom();
  }

  function formatAssistantHtml(value) {
    return String(value || '')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/(^|[\s>])\*([^*\n]+)\*(?=[\s<]|$)/g, '$1<em>$2</em>');
  }

  function typeReply(container, html, done) {
    const text = html.replace(/<[^>]+>/g, '');
    container.innerHTML = '';
    let position = 0;
    const interval = setInterval(function () {
      container.textContent = text.slice(0, ++position);
      scrollBottom();
      if (position >= text.length) {
        clearInterval(interval);
        container.classList.remove('typing-text');
        container.innerHTML = html;
        if (done) done();
      }
    }, 18);
  }

  function addMapReply(html) {
    if (!html) return;
    const d = document.createElement('div');
    d.className = 's-msg-map';
    d.innerHTML = html;
    el.messages.appendChild(d);
    scrollBottom();
  }

  function sanitize(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function setChips(chips) {
    el.chips.innerHTML = '';
    if (!chips || chips.length === 0) return;
    chips.forEach(text => {
      const btn = document.createElement('button');
      btn.className = 's-chip';
      btn.textContent = text;
      btn.addEventListener('click', () => send(text));
      el.chips.appendChild(btn);
    });
  }

  function renderWorkerCards(workers) {
    if (!workers || workers.length === 0) return '';
    let html = '<div class="s-worker-list">';
    workers.slice(0, 5).forEach(w => {
      const pct = w.match_percent || 0;
      const pctClass = pct >= 80 ? 'high' : pct >= 60 ? 'mid' : 'low';
      const avatar = w.avatar
        ? '<img src="' + w.avatar + '" alt="" class="s-worker-avatar-img">'
        : '<div class="s-worker-initials">' + (w.initials || '??') + '</div>';
      html += '<div class="s-worker-card" data-worker-id="' + w.id + '">';
      html += '<div class="s-worker-avatar">' + avatar + '</div>';
      html += '<div class="s-worker-info">';
      html += '<div class="s-worker-name">' + sanitize(w.name) + '</div>';
      html += '<div class="s-worker-meta">' + sanitize(w.category) + ' &middot; \u20B1' + (w.price || 0) + '/hr &middot; ' + sanitize(w.distance || '') + (w.location_approximate ? ' <span style="color:#9aa4b2;font-size:.65rem;">~ approximate</span>' : '') + '</div>';
      html += '<div class="s-worker-footer">';
      html += '<span class="s-worker-rating"><i class="fa-solid fa-star"></i> ' + (w.rating || '0').toFixed(1) + '</span>';
      html += '<span class="s-worker-match ' + pctClass + '">' + pct + '% match</span>';
      html += '<span style="margin-left:auto;"></span><a class="s-worker-action" href="tel:' + (w.phone || '') + '" aria-label="Call ' + sanitize(w.name) + '" title="Call"><i class="fa-solid fa-phone"></i></a>';
      html += '<a class="s-worker-action" href="/client/messages/start?worker_id=' + w.id + '" aria-label="Message ' + sanitize(w.name) + '" title="Message"><i class="fa-regular fa-comment"></i></a>';
      html += '</div></div></div>';
    });
    html += '</div>';
    return html;
  }

  function selectWorker(workerId) {
    document.querySelectorAll('.s-worker-card').forEach(function (card) {
      card.classList.toggle('is-selected', String(card.dataset.workerId) === String(workerId));
    });
    const item = suggestionWorkers.find(function (worker) { return String(worker.id) === String(workerId); });
    const marker = suggestionMarkers.find(function (entry) { return String(entry.id) === String(workerId); });
    suggestionMarkers.forEach(function (entry) {
      if (entry.marker._icon) entry.marker._icon.classList.toggle('is-active', String(entry.id) === String(workerId));
    });
    if (marker && suggestionMap) {
      suggestionMap.setView(marker.marker.getLatLng(), Math.max(suggestionMap.getZoom(), 14), { animate: true });
      marker.marker.openPopup();
    }
    if (item) updateMapCount(suggestionWorkers.length, item.name);
  }

  function updateMapCount(count, selectedName) {
    const countEl = document.getElementById('suggestion-map-count');
    if (!countEl) return;
    countEl.textContent = selectedName ? selectedName + ' selected' : count + ' recommended worker' + (count === 1 ? '' : 's');
  }

  function showClientLocation(position, shouldCenter) {
    if (!suggestionMap) return;
    const locationControl = document.getElementById('suggestion-map-location');
    const latLng = [position.coords.latitude, position.coords.longitude];
    clientLocation = { latLng: latLng, accuracy: Number(position.coords.accuracy) || 0 };
    if (locationControl) {
      locationControl.title = 'Center on my current location';
      locationControl.setAttribute('aria-label', 'Center on my current location');
    }
    if (clientLocationMarker) suggestionMap.removeLayer(clientLocationMarker);
    if (clientLocationAccuracy) suggestionMap.removeLayer(clientLocationAccuracy);
    clientLocationAccuracy = L.circle(latLng, {
      radius: clientLocation.accuracy,
      color: '#2563eb',
      weight: 1,
      fillColor: '#60a5fa',
      fillOpacity: .12,
    }).addTo(suggestionMap);
    clientLocationMarker = L.marker(latLng, {
      icon: L.divIcon({
        className: 'suggestion-client-marker',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
      }),
      zIndexOffset: 1000,
    }).addTo(suggestionMap);
    clientLocationMarker.bindPopup('<div class="suggestion-popup suggestion-client-location-popup"><strong>Your current location</strong><span>Location is provided by your device</span></div>');
    if (shouldCenter) suggestionMap.setView(latLng, Math.max(suggestionMap.getZoom(), 14), { animate: true });
  }

  function requestClientLocation() {
    const locationControl = document.getElementById('suggestion-map-location');
    if (!navigator.geolocation) {
      if (locationControl) {
        locationControl.title = 'Current location is not supported by this browser';
        locationControl.setAttribute('aria-label', 'Current location is not supported by this browser');
      }
      return;
    }
    navigator.geolocation.getCurrentPosition(function (position) {
      showClientLocation(position, true);
    }, function () {
      if (locationControl) {
        locationControl.title = 'Allow location access to show your current location';
        locationControl.setAttribute('aria-label', 'Allow location access to show your current location');
      }
    }, {
      enableHighAccuracy: true,
      maximumAge: 60000,
      timeout: 10000,
    });
  }

  function initSuggestionMap() {
    loadLeaflet(function () {
      const mapEl = document.getElementById('suggestion-map');
      if (!mapEl || suggestionMap) return;
      const center = [13.9581, 120.7278];
      suggestionMap = L.map(mapEl, { zoomControl: false, attributionControl: true }).setView(center, 12);
      L.control.zoom({ position: 'bottomright' }).addTo(suggestionMap);
      suggestionMap._streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap',
      }).addTo(suggestionMap);
      suggestionMap._lightLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap &copy; CARTO',
      });
      setTimeout(function () { suggestionMap.invalidateSize(); }, 100);
      requestClientLocation();
    });
  }

  function updateSuggestionMap(workers) {
    suggestionWorkers = workers || [];
    const empty = document.getElementById('suggestion-map-empty');
    if (empty) empty.style.display = suggestionWorkers.length ? 'none' : 'flex';
    updateMapCount(suggestionWorkers.length);
    loadLeaflet(function () {
      initSuggestionMap();
      setTimeout(function () {
        if (!suggestionMap) return;
        suggestionMarkers.forEach(function (entry) { suggestionMap.removeLayer(entry.marker); });
        suggestionMarkers = [];
        const bounds = [];
        suggestionWorkers.slice(0, 10).forEach(function (w) {
          const lat = Number(w.latitude) || 13.9581;
          const lng = Number(w.longitude) || 120.7278;
          const pct = Number(w.match_percent) || 0;
          const color = '#2563eb';
          const icon = L.divIcon({
            className: 'suggestion-worker-marker',
            html: '<div class="suggestion-marker-pin" style="--marker-color:' + color + '"><span>★ ' + Number(w.rating || 0).toFixed(1) + '</span></div>',
            iconSize: [74, 34],
            iconAnchor: [37, 34],
            popupAnchor: [0, -30],
          });
          const marker = L.marker([lat, lng], { icon: icon }).addTo(suggestionMap);
          marker.bindPopup('<div class="suggestion-popup"><strong>' + sanitize(w.name) + '</strong><span>' + sanitize(w.category || '') + ' · ₱' + (w.price || 0) + '/hr</span><b>' + pct + '% match</b><a href="/client/workers/' + w.id + '">View Profile →</a></div>');
          marker.on('click', function () { selectWorker(w.id); });
          suggestionMarkers.push({ id: w.id, marker: marker });
          bounds.push([lat, lng]);
        });
        if (clientLocation) bounds.push(clientLocation.latLng);
        if (bounds.length > 1) suggestionMap.fitBounds(bounds, { padding: [40, 40] });
        else if (bounds.length === 1) suggestionMap.setView(bounds[0], 14);
        suggestionMap.invalidateSize();
      }, 120);
    });
  }

  function getMarkerColor(pct) {
    return pct >= 80 ? '#28a745' : pct >= 60 ? '#e6a817' : '#dc3545';
  }

  function loadLeaflet(cb) {
    if (typeof L !== 'undefined') { cb(); return; }
    if (!document.querySelector('script[src*="ionicons"]')) {
      var io = document.createElement('script');
      io.src = 'https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js';
      io.type = 'module';
      document.head.appendChild(io);
    }
    if (!document.querySelector('link[href*="leaflet.css"]')) {
      var l = document.createElement('link');
      l.rel = 'stylesheet';
      l.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      document.head.appendChild(l);
    }
    var s = document.createElement('script');
    s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    s.onload = cb;
    s.onerror = function () { console.warn('Leaflet failed to load'); };
    document.head.appendChild(s);
  }

  function addMapToMessages(mapId) {
    loadLeaflet(function () {
      try {
        var container = document.getElementById(mapId);
        if (!container) return;

        var TUY = [13.9581, 120.7278];
        var map = L.map(mapId, { zoomControl: false }).setView(TUY, 12);
        container._mapInstance = map;
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 18,
          attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        var containerData = container.getAttribute('data-workers');
        var workers = containerData ? JSON.parse(containerData) : [];
        var markers = [];

        workers.slice(0, 10).forEach(function (w) {
          var lat = w.latitude || TUY[0];
          var lng = w.longitude || TUY[1];
          if (!lat || !lng) return;
          var pct = w.match_percent || 0;
          var color = getMarkerColor(pct);
          var approx = w.location_approximate;
          var approxBadge = approx
            ? '<div style="position:absolute;top:-4px;right:-4px;width:14px;height:14px;border-radius:50%;background:#fff;border:1.5px solid ' + color + ';display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:700;color:' + color + ';box-shadow:0 1px 2px rgba(0,0,0,0.2);">~</div>'
            : '';
          var icon = L.divIcon({
            className: '',
            html: '<div style="position:relative;width:34px;height:42px;">' +
              '<div style="position:absolute;top:0;left:1px;width:32px;height:32px;border-radius:50%;background:' + color + ';display:flex;align-items:center;justify-content:center;box-shadow:0 1px 3px rgba(0,0,0,0.3);border:2px solid #fff;">' +
              '<ion-icon name="body-outline" style="color:#fff;font-size:18px;"></ion-icon></div>' +
              '<div style="position:absolute;bottom:3px;left:11px;width:12px;height:12px;background:' + color + ';clip-path:polygon(50% 100%,0 0,100% 0);"></div>' +
              approxBadge + '</div>',
            iconSize: [34, 42],
            iconAnchor: [17, 42],
            popupAnchor: [0, -44],
          });
          var marker = L.marker([lat, lng], { icon: icon }).addTo(map);
          markers.push(marker);
          marker.bindPopup(
            '<div style="font-family:Inter,sans-serif;font-size:13px;line-height:1.5;">' +
            '<strong>' + sanitize(w.name) + '</strong><br>' +
            sanitize(w.category) + ' &middot; \u20B1' + (w.price || 0) + '/hr<br>' +
            (w.distance ? sanitize(w.distance) + (approx ? ' <span style="color:#9aa4b2;font-size:11px;">(~ approximate location)</span><br>' : '<br>') : '') +
            '\u2605 ' + (w.rating || '0').toFixed(1) + ' &middot; <span style="color:' + color + ';font-weight:600;">' + pct + '% match</span><br>' +
            '<a href="/client/workers/' + w.id + '" style="color:#1A6FC4;font-size:12px;">View Profile &rarr;</a>' +
            '</div>'
          );
        });

        if (markers.length > 1) {
          map._lastFitBounds = L.featureGroup(markers).getBounds().pad(0.15);
          map.fitBounds(map._lastFitBounds);
        }
        setTimeout(function () { map.invalidateSize(); }, 100);
      } catch (e) {
        console.warn('Map render error:', e);
      }
    });
  }

  function renderMap(workers) {
    if (!workers || workers.length === 0) return '';
    var mapId = 's-map-' + Date.now();
    var data = JSON.stringify(workers.slice(0, 10));
    var html = '<div class="s-map-wrap" id="s-map-wrap-' + mapId + '">' +
      '<button class="s-map-expand-btn" title="Expand map" aria-label="Expand map">' +
        '<ion-icon name="expand-outline"></ion-icon>' +
      '</button>' +
      '<div id="' + mapId + '" class="s-map" data-workers=\'' + data + '\'></div>' +
    '</div>';

    setTimeout(function () {
      addMapToMessages(mapId);
    }, 100);

    return html;
  }

  function send(text) {
    const msg = (text || el.input.value).trim();
    if (!msg) return;
    el.input.value = '';
    el.send.disabled = true;
    setChips([]);
    addMsg('user', msg);
    showTyping();

    fetch('/api/chat/suggest', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ message: msg, history: history.slice(-20) }),
    })
    .then(r => r.json())
    .then(data => {
      hideTyping();
      if (data.success && data.reply) {
        addBotReply(data.reply);
        if (data.workers && data.workers.length > 0) {
          addBotReply(renderWorkerCards(data.workers), false);
          updateSuggestionMap(data.workers);
        }
        setChips(data.suggestions || []);
      } else {
        addBotReply('Sorry, I couldn\'t process that. Please try again.');
        setChips(['Looking for a plumber', 'Need an electrician', 'Best rated workers']);
      }
    })
    .catch(() => {
      hideTyping();
      addBotReply('Having trouble connecting. Please try again later.');
      setChips(['Looking for a plumber', 'Need an electrician', 'Best rated workers']);
    })
    .finally(() => {
      el.send.disabled = false;
      el.input.focus();
    });
  }

  el.send.addEventListener('click', () => send());
  el.messages.addEventListener('click', function (event) {
    const card = event.target.closest('.s-worker-card');
    if (card && !event.target.closest('.s-worker-action')) selectWorker(card.dataset.workerId);
  });
  document.getElementById('suggestion-map-layer')?.addEventListener('click', function () {
    if (!suggestionMap) return;
    const next = mapLayer === 'street' ? 'light' : 'street';
    mapLayer = next;
    suggestionMap.removeLayer(next === 'light' ? suggestionMap._streetLayer : suggestionMap._lightLayer);
    (next === 'light' ? suggestionMap._lightLayer : suggestionMap._streetLayer).addTo(suggestionMap);
  });
  document.getElementById('suggestion-map-location')?.addEventListener('click', function () {
    if (clientLocation) {
      suggestionMap.setView(clientLocation.latLng, Math.max(suggestionMap.getZoom(), 14), { animate: true });
      if (clientLocationMarker) clientLocationMarker.openPopup();
      return;
    }
    requestClientLocation();
  });
  el.input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
  });

  setChips([
    'Looking for a plumber',
    'Need an electrician',
    'Best rated workers near me',
    'How do I book?',
  ]);

  el.messages.addEventListener('click', function (e) {
    var btn = e.target.closest('.s-map-expand-btn');
    if (!btn) return;
    e.stopPropagation();
    var wrap = btn.closest('.s-map-wrap');
    el.messages.classList.toggle('map-expanded');
    var expanded = el.messages.classList.contains('map-expanded');
    var icon = btn.querySelector('ion-icon');
    icon.setAttribute('name', expanded ? 'contract-outline' : 'expand-outline');
    btn.setAttribute('title', expanded ? 'Collapse map' : 'Expand map');
    btn.setAttribute('aria-label', expanded ? 'Collapse map' : 'Expand map');
    var mapEl = wrap.querySelector('.s-map');
    if (mapEl && mapEl._mapInstance) {
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          var map = mapEl._mapInstance;
          map.invalidateSize();
          if (expanded && map._lastFitBounds) {
            map.fitBounds(map._lastFitBounds);
          }
        });
      });
    }
  });
  initSuggestionMap();
})();
</script>
@endpush