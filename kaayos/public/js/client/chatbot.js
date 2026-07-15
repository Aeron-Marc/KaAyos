(function () {
  'use strict';

  const CSRF_TOKEN = document.querySelector('#kaayos-chatbot')?.dataset?.csrf || '';

  const elements = {
    fab:        document.getElementById('chat-fab'),
    fabIcon:    document.getElementById('chat-fab-icon'),
    fabClose:   document.getElementById('chat-fab-close'),
    window:     document.getElementById('chat-window'),
    minimize:   document.getElementById('chat-minimize'),
    messages:   document.getElementById('chat-messages'),
    suggestions: document.getElementById('chat-suggestions'),
    input:      document.getElementById('chat-input'),
    send:       document.getElementById('chat-send'),
  };

  let isOpen = false;
  let conversationHistory = [];

  // ── Toggle chat ──
  function open() {
    isOpen = true;
    elements.window.style.display = 'flex';
    elements.fabIcon.style.display = 'none';
    elements.fabClose.style.display = 'inline';
    setTimeout(() => elements.input?.focus(), 300);
    scrollToBottom();
  }

  function close() {
    isOpen = false;
    elements.window.style.display = 'none';
    elements.fabIcon.style.display = 'inline';
    elements.fabClose.style.display = 'none';
  }

  function toggle() { isOpen ? close() : open(); }

  // ── Scroll ──
  function scrollToBottom() {
    setTimeout(() => {
      elements.messages.scrollTop = elements.messages.scrollHeight;
    }, 50);
  }

  // ── Typing indicator ──
  function showTyping() {
    const div = document.createElement('div');
    div.className = 'chat-msg bot typing-indicator';
    div.id = 'chat-typing';
    div.innerHTML = '<div class="msg-bubble"><span></span><span></span><span></span></div>';
    elements.messages.appendChild(div);
    scrollToBottom();
  }

  function hideTyping() {
    const el = document.getElementById('chat-typing');
    if (el) el.remove();
  }

  // ── Add message ──
  function addMessage(role, text) {
    const div = document.createElement('div');
    div.className = 'chat-msg ' + role;

    const bubble = document.createElement('div');
    bubble.className = 'msg-bubble';
    bubble.innerHTML = '<p>' + sanitize(text).replace(/\n/g, '<br>') + '</p>';

    const time = document.createElement('span');
    time.className = 'msg-time';
    time.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    bubble.appendChild(time);
    div.appendChild(bubble);
    elements.messages.appendChild(div);
    scrollToBottom();

    conversationHistory.push({ role, content: text });
  }

  // ── Render bot reply (may contain HTML like <a>) ──
  function addBotReply(html) {
    const div = document.createElement('div');
    div.className = 'chat-msg bot';

    const bubble = document.createElement('div');
    bubble.className = 'msg-bubble';
    bubble.innerHTML = html;

    const time = document.createElement('span');
    time.className = 'msg-time';
    time.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    bubble.appendChild(time);
    div.appendChild(bubble);
    elements.messages.appendChild(div);
    scrollToBottom();

    conversationHistory.push({ role: 'assistant', content: html.replace(/<[^>]*>/g, '') });
  }

  // ── Suggestions ──
  function setSuggestions(chips) {
    elements.suggestions.innerHTML = '';
    if (!chips || chips.length === 0) return;
    chips.forEach(text => {
      const btn = document.createElement('button');
      btn.className = 'suggestion-chip';
      btn.textContent = text;
      btn.dataset.text = text;
      btn.addEventListener('click', () => sendMessage(text));
      elements.suggestions.appendChild(btn);
    });
  }

  // ── Send ──
  function sendMessage(text) {
    const msg = (text || elements.input.value).trim();
    if (!msg) return;

    elements.input.value = '';
    elements.send.disabled = true;
    setSuggestions([]);
    addMessage('user', msg);

    showTyping();

    fetch('/api/chat', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        message: msg,
        history: conversationHistory.slice(-20),
      }),
    })
    .then(r => r.json())
    .then(data => {
      hideTyping();
      if (data.success && data.reply) {
        addBotReply(data.reply);
        setSuggestions(data.suggestions || []);
      } else {
        addBotReply('I\'m sorry, I couldn\'t process that. Please try again.');
        setSuggestions(['How do I book a worker?', 'What areas do you serve?']);
      }
    })
    .catch(() => {
      hideTyping();
      addBotReply('I\'m having trouble connecting. Please try again later.');
      setSuggestions(['How do I book a worker?', 'What areas do you serve?']);
    })
    .finally(() => {
      elements.send.disabled = false;
      elements.input.focus();
    });
  }

  // ── Sanitize ──
  function sanitize(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // ── Event listeners ──
  elements.fab.addEventListener('click', toggle);
  elements.minimize.addEventListener('click', close);

  elements.send.addEventListener('click', () => sendMessage());
  elements.input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  // Suggestion chips in the initial state
  document.querySelectorAll('.suggestion-chip').forEach(btn => {
    btn.addEventListener('click', () => sendMessage(btn.dataset.text));
  });
})();
