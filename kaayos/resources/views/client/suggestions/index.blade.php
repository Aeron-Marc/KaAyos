@extends('layouts.client')

@section('title', 'Suggestions')
@section('page_title', 'Suggestions')

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
<div id="suggestion-chat" class="suggestion-chat"
     data-csrf="{{ csrf_token() }}">
  <div class="suggestion-chat-header">
    <div class="suggestion-chat-avatar">
      <i class="fa-solid fa-lightbulb"></i>
    </div>
    <div>
      <div class="suggestion-chat-title">KaAyos Suggestions</div>
      <div class="suggestion-chat-status">AI-powered recommendations</div>
    </div>
  </div>

  <div id="suggestion-messages" class="suggestion-messages">
    <div class="s-msg bot">
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
</div>
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
    d.appendChild(b);
    el.messages.appendChild(d);
    scrollBottom();
    history.push({ role, content: text });
  }

  function addBotReply(html) {
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    const d = document.createElement('div');
    d.className = 's-msg bot';
    const b = document.createElement('div');
    b.className = 's-msg-bubble';
    b.innerHTML = html;
    const t = document.createElement('span');
    t.className = 's-msg-time';
    t.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    b.appendChild(t);
    d.appendChild(b);
    el.messages.appendChild(d);
    scrollBottom();
    history.push({ role: 'assistant', content: b.textContent || '' });
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
      html += '<a href="/client/workers/' + w.id + '" class="s-worker-card">';
      html += '<div class="s-worker-avatar">' + avatar + '</div>';
      html += '<div class="s-worker-info">';
      html += '<div class="s-worker-name">' + sanitize(w.name) + '</div>';
      html += '<div class="s-worker-meta">' + sanitize(w.category) + ' &middot; \u20B1' + (w.price || 0) + '/hr &middot; ' + sanitize(w.distance || '') + (w.location_approximate ? ' <span style="color:#9aa4b2;font-size:.65rem;">~ approximate</span>' : '') + '</div>';
      html += '<div class="s-worker-footer">';
      html += '<span class="s-worker-rating"><i class="fa-solid fa-star"></i> ' + (w.rating || '0').toFixed(1) + '</span>';
      html += '<span class="s-worker-match ' + pctClass + '">' + pct + '% match</span>';
      html += '</div></div></a>';
    });
    html += '</div>';
    return html;
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
          addBotReply(renderWorkerCards(data.workers));
          addMapReply(renderMap(data.workers));
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
})();
</script>
@endpush