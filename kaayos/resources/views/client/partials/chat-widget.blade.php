<div id="kaayos-chatbot" data-csrf="{{ csrf_token() }}" data-route="{{ route('home') }}">
  <button id="chat-fab" class="chat-fab" aria-label="Open chat">
    <i class="fa-solid fa-comment-dots" id="chat-fab-icon"></i>
    <i class="fa-solid fa-xmark" id="chat-fab-close" style="display:none"></i>
  </button>

  <div id="chat-window" class="chat-window" style="display:none">
    <div class="chat-header">
      <div class="chat-header-info">
        <div class="chat-avatar">
          <i class="fa-solid fa-robot"></i>
        </div>
        <div>
          <div class="chat-title">KaAyos Assistant</div>
          <div class="chat-status">Online</div>
        </div>
      </div>
      <button id="chat-minimize" class="chat-minimize" aria-label="Minimize">
        <i class="fa-solid fa-minus"></i>
      </button>
    </div>

    <div id="chat-messages" class="chat-messages">
      <div class="chat-msg bot">
        <div class="msg-bubble">
          <p>Hello! 👋 I'm the KaAyos assistant. How can I help you today?</p>
          <span class="msg-time">Just now</span>
        </div>
      </div>
    </div>

    <div id="chat-suggestions" class="chat-suggestions">
      <button class="suggestion-chip" data-text="How do I book a worker?">How do I book a worker?</button>
      <button class="suggestion-chip" data-text="What areas do you serve?">What areas do you serve?</button>
      <button class="suggestion-chip" data-text="How are workers verified?">How are workers verified?</button>
    </div>

    <div class="chat-input-bar">
      <input
        type="text"
        id="chat-input"
        class="chat-input"
        placeholder="Type a message..."
        maxlength="1000"
        autocomplete="off"
      />
      <button id="chat-send" class="chat-send" aria-label="Send">
        <i class="fa-solid fa-paper-plane"></i>
      </button>
    </div>
  </div>
</div>

@push('styles')
<style>
#kaayos-chatbot {
  --chat-primary: #1A6FC4;
  --chat-bg: #ffffff;
  --chat-shadow: 0 8px 32px rgba(0,0,0,0.18);
  font-family: 'Inter', sans-serif;
}

.chat-fab {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--chat-primary);
  color: #fff;
  border: none;
  cursor: pointer;
  font-size: 1.3rem;
  box-shadow: 0 4px 16px rgba(26,111,196,0.35);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, box-shadow 0.2s;
}
.chat-fab:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 24px rgba(26,111,196,0.45);
}

.chat-window {
  position: fixed;
  bottom: 92px;
  right: 24px;
  width: 380px;
  max-width: calc(100vw - 48px);
  height: 560px;
  max-height: calc(100vh - 140px);
  background: var(--chat-bg);
  border-radius: 16px;
  box-shadow: var(--chat-shadow);
  z-index: 9998;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: chatSlideUp 0.25s ease;
}
@keyframes chatSlideUp {
  from { opacity: 0; transform: translateY(16px) scale(0.96); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.chat-header {
  background: var(--chat-primary);
  color: #fff;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.chat-header-info {
  display: flex;
  align-items: center;
  gap: 10px;
}
.chat-avatar {
  width: 36px;
  height: 36px;
  background: rgba(255,255,255,0.18);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.chat-title {
  font-weight: 600;
  font-size: 0.9rem;
}
.chat-status {
  font-size: 0.72rem;
  opacity: 0.78;
}
.chat-minimize {
  background: none;
  border: none;
  color: rgba(255,255,255,0.78);
  cursor: pointer;
  font-size: 1rem;
  padding: 4px;
  border-radius: 6px;
  transition: background 0.15s;
}
.chat-minimize:hover {
  background: rgba(255,255,255,0.12);
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  scroll-behavior: smooth;
}
.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

.chat-msg {
  display: flex;
  max-width: 88%;
  animation: msgFadeIn 0.2s ease;
}
@keyframes msgFadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
.chat-msg.bot { align-self: flex-start; }
.chat-msg.user { align-self: flex-end; }

.msg-bubble {
  padding: 10px 14px;
  border-radius: 14px;
  line-height: 1.55;
  font-size: 0.88rem;
  word-wrap: break-word;
  position: relative;
}
.chat-msg.bot .msg-bubble {
  background: #f0f4f8;
  color: #1B2430;
  border-bottom-left-radius: 4px;
}
.chat-msg.user .msg-bubble {
  background: var(--chat-primary);
  color: #fff;
  border-bottom-right-radius: 4px;
}
.msg-bubble p { margin: 0; }
.msg-bubble a { color: inherit; text-decoration: underline; }
.msg-time {
  display: block;
  font-size: 0.65rem;
  opacity: 0.55;
  margin-top: 4px;
}

.typing-indicator .msg-bubble {
  background: #f0f4f8;
  display: flex;
  gap: 4px;
  padding: 14px 18px;
}
.typing-indicator .msg-bubble span {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #8C97A4;
  animation: typingBounce 1.4s infinite;
}
.typing-indicator .msg-bubble span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator .msg-bubble span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingBounce {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-5px); }
}

.chat-suggestions {
  padding: 0 16px 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  flex-shrink: 0;
}
.suggestion-chip {
  background: #e6f1fb;
  color: #0C447C;
  border: 1px solid #b5d4f4;
  border-radius: 100px;
  padding: 5px 12px;
  font-size: 0.78rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
  font-family: inherit;
}
.suggestion-chip:hover {
  background: #85B7EB;
  color: #fff;
  border-color: #85B7EB;
}

.chat-input-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px 12px;
  border-top: 1px solid #e8ecf0;
  flex-shrink: 0;
}
.chat-input {
  flex: 1;
  border: 1.5px solid #e8ecf0;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.88rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.15s;
}
.chat-input:focus {
  border-color: var(--chat-primary);
}
.chat-send {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--chat-primary);
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
.chat-send:hover { background: #185FA5; }
.chat-send:disabled {
  background: #b5d4f4;
  cursor: not-allowed;
}

@media (max-width: 520px) {
  .chat-window {
    right: 0;
    bottom: 0;
    width: 100vw;
    max-width: 100vw;
    max-height: 100vh;
    height: 100vh;
    border-radius: 0;
  }
  .chat-fab {
    bottom: 16px;
    right: 16px;
  }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/client/chatbot.js') }}"></script>
@endpush
