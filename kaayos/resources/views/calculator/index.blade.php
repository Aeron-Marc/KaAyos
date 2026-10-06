<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Cost Calculator – KaAyos</title>
<link rel="icon" href="{{ asset('images/KaAyos_logo.jpeg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--b9:#042C53;--b8:#0C447C;--b7:#185FA5;--b6:#1A6FC4;--b4:#378ADD;--b2:#85B7EB;--b1:#B5D4F4;--b0:#E6F1FB;--g9:#1B2430;--g7:#3D4A56;--g4:#8C97A4;--g1:#E8ECF0;--white:#fff;--off:#F7F8FA;--amber:#f5a623;--amber-hover:#d4891a}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;color:var(--g7);background:var(--off);line-height:1.6;font-size:16px}
a{text-decoration:none;color:inherit}
[hidden]{display:none!important}
.nav{background:var(--b9);padding:0 5%;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.nav-logo{display:flex;align-items:center;gap:9px}
.logo-box{width:50px;height:50px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px}
.logo-box img{width:100%;height:100%;object-fit:contain}
.nav-logo span{font-size:1.4rem;font-weight:700;color:#fff;letter-spacing:.02em}
.nav-links{display:flex;gap:26px;list-style:none}
.nav-links a{font-size:.875rem;font-weight:500;color:rgba(255,255,255,.72);transition:color .18s;position:relative}
.nav-links a::after{content:'';position:absolute;bottom:-4px;left:0;width:0;height:2px;background:var(--amber);transition:width .25s;border-radius:1px}
.nav-links a:hover{color:#fff}
.nav-links a:hover::after{width:100%}
.nav-links a.active{color:#fff}
.nav-links a.active::after{width:100%}
.nav-cta{display:flex;gap:9px}
.btn{font-size:.875rem;font-weight:600;border-radius:7px;padding:8px 18px;cursor:pointer;border:none;transition:all .18s;white-space:nowrap;display:inline-flex;align-items:center;gap:7px}
.btn-ghost{background:transparent;color:rgba(255,255,255,.82);border:1.5px solid rgba(255,255,255,.3)}
.btn-ghost:hover{border-color:rgba(255,255,255,.7);color:#fff}
.btn-amber{background:var(--amber);color:#fff}
.btn-amber:hover{background:var(--amber-hover)}
.btn-primary{background:var(--amber);color:#fff;box-shadow:0 4px 14px rgba(245,166,35,.35)}
.btn-primary:hover{background:var(--amber-hover);transform:translateY(-1px);box-shadow:0 6px 20px rgba(245,166,35,.4)}
.btn-outline{background:transparent;color:var(--b6);border:1.5px solid var(--b4);font-size:.875rem;font-weight:600;padding:8px 18px;border-radius:7px;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:7px}
.btn-outline:hover{background:var(--b0)}

.page-header{background:var(--b9);padding:48px 5% 40px;text-align:center}
.page-header h1{font-size:clamp(1.8rem,3vw,2.4rem);font-weight:700;color:#fff;margin-bottom:8px}
.page-header p{font-size:.93rem;color:rgba(255,255,255,.6);max-width:640px;margin:0 auto}

.notice-wrap{max-width:1200px;margin:20px auto 0;padding:0 5%}
.est-notice{display:flex;align-items:flex-start;gap:9px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;padding:10px 14px;border-radius:9px;font-size:.84rem;line-height:1.5}
.est-notice i{color:#2563eb;margin-top:3px}
.est-notice strong{color:var(--b8)}

.calc-layout{display:flex;gap:28px;max-width:1200px;margin:0 auto;padding:24px 5% 8px;align-items:flex-start}
.calc-form{flex:1;min-width:0}
.calc-card{background:#fff;border:1.5px solid var(--g1);border-radius:16px;padding:24px;box-shadow:0 2px 8px rgba(4,44,83,.06);margin-bottom:20px}
.calc-card-title{font-size:1.05rem;font-weight:700;color:var(--b9);display:flex;align-items:center;gap:10px;margin-bottom:16px}
.calc-card-title .num{width:26px;height:26px;border-radius:50%;background:var(--b6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.82rem;flex-shrink:0}
.field{margin-bottom:14px}
.field:last-child{margin-bottom:0}
.field > label,.field-label{display:block;font-size:.83rem;font-weight:600;color:var(--g7);margin-bottom:6px}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-control{width:100%;border:1.5px solid var(--g1);border-radius:9px;padding:10px 14px;font-size:.93rem;color:var(--g9);background:var(--off);outline:none;font-family:inherit;transition:border-color .18s;box-sizing:border-box}
.form-control:focus{border-color:var(--b4);background:#fff}
.hint{font-size:.78rem;color:var(--g4);display:block;margin-top:5px;line-height:1.45}

.seg{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.seg-opt{position:relative;cursor:pointer;border:1.5px solid var(--g1);border-radius:10px;padding:10px 12px;background:var(--off);transition:all .18s;display:block;text-align:center;font-size:.87rem;font-weight:600;color:var(--g7)}
.seg-opt input{position:absolute;opacity:0;width:0;height:0}
.seg-opt:hover{border-color:var(--b2)}
.seg-opt:has(input:checked){border-color:var(--b6);background:var(--b0);color:var(--b8);box-shadow:0 0 0 1px var(--b6)}
.seg-opt:has(input:focus-visible){outline:2px solid var(--b6);outline-offset:2px}

.urgency-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.urgency-card{position:relative;cursor:pointer;border:1.5px solid var(--g1);border-radius:10px;padding:10px 12px;background:var(--off);transition:all .18s;display:flex;flex-direction:column;gap:2px}
.urgency-card input{position:absolute;opacity:0;width:0;height:0}
.urgency-card:hover{border-color:var(--b2)}
.urgency-card:has(input:checked){border-color:var(--b6);background:var(--b0);box-shadow:0 0 0 1px var(--b6)}
.urgency-card.emg:has(input:checked){border-color:#dc2626;background:#fef2f2;box-shadow:0 0 0 1px #dc2626}
.urgency-card:has(input:focus-visible){outline:2px solid var(--b6);outline-offset:2px}
.urgency-name{font-size:.85rem;font-weight:600;color:var(--g9);display:flex;align-items:center;gap:6px}
.urgency-card.emg .urgency-name{color:#dc2626}
.urgency-desc{font-size:.7rem;color:var(--g4);line-height:1.3}
.urgency-mult{font-size:.68rem;font-weight:700;color:var(--b7);background:#fff;border:1px solid var(--g1);border-radius:20px;padding:1px 8px;align-self:flex-start;margin-top:3px}
.urgency-card.emg .urgency-mult{color:#dc2626;border-color:#fecaca}

.mat-head{display:grid;grid-template-columns:1fr 70px 110px 90px 34px;gap:8px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--g4);margin-bottom:6px;padding:0 2px}
.mat-head span:nth-child(4){text-align:right}
.mat-row{display:grid;grid-template-columns:1fr 70px 110px 90px 34px;gap:8px;align-items:center;margin-bottom:8px}
.mat-row input{min-width:0}
.mat-line{font-size:.85rem;font-weight:600;color:var(--b7);text-align:right;white-space:nowrap}
.mat-remove{width:34px;height:34px;border-radius:8px;border:1.5px solid var(--g1);background:#fff;color:var(--g4);cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center}
.mat-remove:hover{border-color:#dc2626;color:#dc2626;background:#fef2f2}
.mat-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:12px}
.mat-subtotal{font-size:.85rem;color:var(--g4)}
.mat-subtotal strong{color:var(--b8)}

.calc-result{flex:0 0 340px;position:sticky;top:84px}
.res-card{background:#fff;border:1.5px solid var(--g1);border-radius:16px;padding:22px;box-shadow:0 2px 8px rgba(4,44,83,.06)}
.res-title{font-size:1rem;font-weight:700;color:var(--b9);display:flex;align-items:center;gap:8px;margin-bottom:14px}
.res-title i{color:var(--b6)}
.res-row{display:flex;justify-content:space-between;gap:10px;font-size:.87rem;color:var(--g7);margin-bottom:6px}
.res-row > span:last-child{font-weight:600;color:var(--g9);text-align:right;min-width:0;overflow-wrap:anywhere}
.res-sub{font-weight:600}
.res-chips{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 8px}
.chip{font-size:.7rem;font-weight:700;background:var(--b0);color:var(--b7);border:1px solid var(--b2);border-radius:20px;padding:2px 10px;white-space:nowrap}
.res-divider{border-top:1px dashed var(--g1);margin:10px 0}
.res-total{display:flex;justify-content:space-between;align-items:center;gap:10px;background:var(--b9);color:#fff;border-radius:12px;padding:14px 16px;margin-top:14px}
.res-total .res-total-label{font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;opacity:.75}
.res-total strong{font-size:1.45rem;white-space:nowrap}
.res-note{font-size:.75rem;color:var(--g4);background:var(--off);border:1px solid var(--g1);border-radius:9px;padding:10px 12px;margin-top:12px;line-height:1.5}
.res-note i{color:var(--b6);margin-right:4px}
.res-cta{width:100%;justify-content:center;margin-top:12px;padding:12px 0;font-size:.92rem}

.js-off{background:#fef2f2;border:1.5px solid #fecaca;color:#b91c1c;border-radius:9px;padding:12px 14px;font-size:.86rem;margin-bottom:16px}

.footer{background:var(--g9);padding:40px 5% 20px;margin-top:36px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:36px;margin-bottom:28px}
.f-brand p{font-size:.84rem;color:rgba(255,255,255,.45);line-height:1.65;max-width:280px;margin-top:10px}
.f-brand .brand{display:flex;align-items:center;gap:9px}
.f-brand .brand span{font-size:1.2rem;font-weight:700;color:#fff}
.f-brand .flogo{width:32px;height:32px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px}
.f-brand .flogo img{width:100%;height:100%;object-fit:contain;border-radius:6px}
.f-title{font-size:.78rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#fff;margin-bottom:10px}
.f-links{list-style:none;display:flex;flex-direction:column;gap:7px}
.f-links a{font-size:.84rem;color:rgba(255,255,255,.45);transition:color .18s;display:inline-flex;align-items:center;gap:6px}
.f-links a:hover{color:var(--b2)}
.f-bottom{border-top:1px solid rgba(255,255,255,.07);padding-top:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.f-bottom p{font-size:.76rem;color:rgba(255,255,255,.3)}
.socials{display:flex;gap:10px}
.soc{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:.9rem;color:rgba(255,255,255,.45);cursor:pointer;transition:all .18s;text-decoration:none}
.soc:hover{background:var(--b6);color:#fff}

@media(max-width:900px){
  .calc-layout{flex-direction:column}
  .calc-result{position:static;flex:none;width:100%;order:-1}
  .nav-links{display:none}
}
@media(max-width:600px){
  .urgency-grid{grid-template-columns:1fr}
  .field-row{grid-template-columns:1fr}
  .mat-head{display:none}
  .mat-row{grid-template-columns:1fr 64px 96px 30px;gap:6px}
  .mat-line{grid-column:1/4;text-align:left;padding-left:2px}
  .footer-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<nav class="nav">
  <a href="/" class="nav-logo">
    <div class="logo-box"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos Logo"></div>
    <span>KaAyos</span>
  </a>
  <ul class="nav-links">
    <li><a href="/">Home</a></li>
    <li><a href="/services">Services</a></li>
    <li><a href="/calculator" class="active">Calculator</a></li>
  </ul>
  <div class="nav-cta">
    <a href="/login" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> Log In</a>
    <a href="/register" class="btn btn-amber"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
  </div>
</nav>

<div class="page-header">
  <h1>Job Cost Calculator</h1>
  <p>Estimate the cost of your job — service, complexity, urgency, and materials — before you book a Trabahador in Tuy, Batangas.</p>
</div>

<div class="notice-wrap">
  <div class="est-notice">
    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
    <span>Every figure here is an <strong>estimate</strong> — the final price is confirmed with your worker once your job scope is reviewed. Actual rates may vary by worker.</span>
  </div>
</div>

<main class="calc-layout">
  <div class="calc-form">
    <noscript>
      <div class="js-off"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> This calculator needs JavaScript enabled. Please enable JavaScript to estimate your job cost.</div>
    </noscript>

    <section class="calc-card">
      <div class="calc-card-title"><span class="num">1</span> Job scope</div>

      <div class="field">
        <label for="service-select">Service</label>
        <select id="service-select" class="form-control">
          <option value="">— Custom base price —</option>
          @foreach($services->groupBy(fn ($s) => $s->category?->name ?? 'Other') as $catName => $catServices)
            <optgroup label="{{ $catName }}">
              @foreach($catServices as $svc)
                <option value="{{ $svc->id }}">{{ $svc->name }} — ₱{{ number_format((float) $svc->base_price, 0) }}</option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
        <small class="hint">Pick a common service with a standard base rate, or leave it on "Custom base price" and enter your own below.</small>
      </div>

      <div class="field" id="custom-price-wrap" hidden>
        <label for="custom-price">Base price (₱)</label>
        <input type="number" id="custom-price" class="form-control" min="0" step="10" value="350" inputmode="decimal">
      </div>

      <div class="field">
        <span class="field-label">Pricing type</span>
        <div class="seg" role="radiogroup" aria-label="Pricing type">
          <label class="seg-opt"><input type="radio" name="pricing_type" value="fixed" checked> Fixed task</label>
          <label class="seg-opt"><input type="radio" name="pricing_type" value="hourly"> Hourly</label>
        </div>
      </div>

      <div class="field-row" id="hourly-fields" hidden>
        <div class="field">
          <label for="hours">Estimated duration (hrs)</label>
          <input type="number" id="hours" class="form-control" min="0.5" max="24" step="0.5" value="2" inputmode="decimal">
        </div>
        <div class="field">
          <label for="hourly-rate">Hourly rate (₱)</label>
          <input type="number" id="hourly-rate" class="form-control" min="0" step="10" value="350" inputmode="decimal">
        </div>
      </div>

      <div class="field">
        <label for="complexity">Complexity</label>
        <select id="complexity" class="form-control">
          <option value="standard">Standard — base rate</option>
          <option value="moderate">Moderate — &times;1.20</option>
          <option value="complex">Complex — &times;1.20</option>
          <option value="high_hazard">High hazard — &times;1.50</option>
          <option value="hazardous">Hazardous — &times;1.50</option>
        </select>
      </div>

      <div class="field">
        <span class="field-label">Urgency</span>
        <div class="urgency-grid" role="radiogroup" aria-label="Urgency">
          <label class="urgency-card urgency-normal">
            <input type="radio" name="urgency" value="normal" checked>
            <span class="urgency-name"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Normal</span>
            <span class="urgency-desc">Standard booking</span>
            <span class="urgency-mult">Base rate</span>
          </label>
          <label class="urgency-card urgency-soon">
            <input type="radio" name="urgency" value="soon">
            <span class="urgency-name"><i class="fa-solid fa-clock" aria-hidden="true"></i> Soon</span>
            <span class="urgency-desc">Within 24 hours</span>
            <span class="urgency-mult">&times;1.10</span>
          </label>
          <label class="urgency-card urgency-emg">
            <input type="radio" name="urgency" value="emergency">
            <span class="urgency-name"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Emergency</span>
            <span class="urgency-desc">Immediate attention</span>
            <span class="urgency-mult">&times;1.25</span>
          </label>
        </div>
      </div>
    </section>

    <section class="calc-card">
      <div class="calc-card-title"><span class="num">2</span> Materials</div>
      <p class="hint" style="margin:0 0 14px">Add the materials you expect to need — line totals and your subtotal update as you type.</p>

      <div class="mat-head" aria-hidden="true">
        <span>Item</span><span>Qty</span><span>Unit price</span><span>Total</span><span></span>
      </div>

      <div id="materials-list"></div>

      <div class="mat-actions">
        <button type="button" class="btn btn-outline" id="add-material"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add material</button>
        <div class="mat-subtotal">Materials subtotal: <strong id="mat-subtotal">₱0.00</strong> &middot; <span id="mat-count">0 items</span></div>
      </div>

      <template id="material-row-tpl">
        <div class="mat-row">
          <input type="text" class="form-control mat-name" placeholder="e.g. PVC pipe" aria-label="Material name" autocomplete="off">
          <input type="number" class="form-control mat-qty" min="0" step="1" value="1" aria-label="Quantity" inputmode="decimal">
          <input type="number" class="form-control mat-price" min="0" step="0.01" placeholder="0.00" aria-label="Unit price" inputmode="decimal">
          <span class="mat-line">₱0.00</span>
          <button type="button" class="mat-remove" aria-label="Remove material"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
      </template>
    </section>
  </div>

  <aside class="calc-result" aria-live="polite">
    <div class="res-card">
      <div class="res-title"><i class="fa-solid fa-receipt" aria-hidden="true"></i> Estimated cost</div>

      <div class="res-row"><span id="res-base-label">Custom base price</span><span id="res-base">₱350.00</span></div>
      <div class="res-chips" id="res-chips"></div>
      <div class="res-row res-sub"><span>Labor subtotal</span><span id="res-labor">₱350.00</span></div>

      <div class="res-divider"></div>

      <div class="res-row"><span id="res-mat-label">Materials (0 items)</span><span id="res-mat">₱0.00</span></div>

      <div class="res-total">
        <span class="res-total-label">Total estimate</span>
        <strong id="res-total">₱350.00</strong>
      </div>

      <div class="res-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Estimate only — the final price is confirmed with your worker after your job scope is reviewed. Actual rates may vary by worker.</div>

      <a href="/search" class="btn btn-primary res-cta"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Find a worker</a>
    </div>
  </aside>
</main>

<footer class="footer">
  <div class="footer-grid">
    <div class="f-brand">
      <a href="/" class="brand">
        <div class="flogo"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos"></div>
        <span>KaAyos</span>
      </a>
      <p>Connecting homeowners with verified skilled workers in Tuy, Batangas.</p>
    </div>
    <div>
      <div class="f-title">Services</div>
      <ul class="f-links">
        <li><a href="/?category=plumbing">Plumbing</a></li>
        <li><a href="/?category=electrical">Electrical</a></li>
        <li><a href="/#services">View all</a></li>
      </ul>
    </div>
    <div>
      <div class="f-title">Company</div>
      <ul class="f-links">
        <li><a href="/about">About KaAyos</a></li>
        <li><a href="/contact">Contact</a></li>
        <li><a href="/privacy">Privacy Policy</a></li>
        <li><a href="/terms">Terms of Service</a></li>
        <li><a href="/safety">Safety</a></li>
      </ul>
    </div>
  </div>
  <div class="f-bottom">
    <p>&copy; 2026 KaAyos</p>
    <div class="socials">
      <a href="https://facebook.com/kaayos" class="soc" title="Facebook" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
      <a href="mailto:hello@kaayos.tech" class="soc" title="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
  </div>
</footer>

<script type="application/json" id="services-data">{!! json_encode($services->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'base_price' => $s->base_price])->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script>
(function () {
    'use strict';

    var services = [];
    try {
        services = JSON.parse(document.getElementById('services-data').textContent || '[]');
    } catch (e) {
        services = [];
    }

    var COMPLEXITY_MULT = { standard: 1, moderate: 1.2, complex: 1.2, high_hazard: 1.5, hazardous: 1.5 };
    var URGENCY_MULT = { normal: 1, soon: 1.1, emergency: 1.25 };

    function byId(id) { return document.getElementById(id); }

    function num(value, fallback) {
        var n = parseFloat(value);
        if (!isFinite(n) || n < 0) return fallback === undefined ? 0 : fallback;
        return n;
    }

    function peso(n) {
        return '\u20B1' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getPricingType() {
        var checked = document.querySelector('input[name="pricing_type"]:checked');
        return checked ? checked.value : 'fixed';
    }

    function getUrgency() {
        var checked = document.querySelector('input[name="urgency"]:checked');
        return checked ? checked.value : 'normal';
    }

    function findService(id) {
        for (var i = 0; i < services.length; i++) {
            if (String(services[i].id) === String(id)) return services[i];
        }
        return null;
    }

    function clearNode(node) {
        while (node.firstChild) node.removeChild(node.firstChild);
    }

    function addChip(parent, text) {
        var chip = document.createElement('span');
        chip.className = 'chip';
        chip.textContent = text;
        parent.appendChild(chip);
    }

    function recalc() {
        var hourly = getPricingType() === 'hourly';
        var serviceId = byId('service-select').value;
        var svc = serviceId ? findService(serviceId) : null;
        var customWrap = byId('custom-price-wrap');

        var laborBase;
        var baseLabel;

        if (hourly) {
            customWrap.hidden = true;
            var rate = num(byId('hourly-rate').value, 0);
            var hours = num(byId('hours').value, 2);
            if (hours < 0.5) hours = 0.5;
            if (hours > 24) hours = 24;
            laborBase = rate * hours;
            baseLabel = hours + ' hrs @ ' + peso(rate) + '/hr';
        } else if (svc) {
            customWrap.hidden = true;
            laborBase = num(svc.base_price, 0);
            baseLabel = svc.name;
        } else {
            customWrap.hidden = false;
            laborBase = num(byId('custom-price').value, 0);
            baseLabel = 'Custom base price';
        }

        var complexity = byId('complexity').value || 'standard';
        var cMult = COMPLEXITY_MULT[complexity] || 1;
        var urgency = getUrgency();
        var uMult = URGENCY_MULT[urgency] || 1;

        var labor = Math.round(laborBase * cMult * uMult * 100) / 100;

        var rows = byId('materials-list').querySelectorAll('.mat-row');
        var materialsTotal = 0;
        var items = 0;
        for (var i = 0; i < rows.length; i++) {
            var qty = num(rows[i].querySelector('.mat-qty').value, 0);
            var price = num(rows[i].querySelector('.mat-price').value, 0);
            var line = Math.round(qty * price * 100) / 100;
            rows[i].querySelector('.mat-line').textContent = peso(line);
            var name = (rows[i].querySelector('.mat-name').value || '').trim();
            if (name !== '' || line > 0) {
                items += 1;
                materialsTotal += line;
            }
        }
        materialsTotal = Math.round(materialsTotal * 100) / 100;

        var total = Math.round((labor + materialsTotal) * 100) / 100;

        byId('res-base-label').textContent = baseLabel;
        byId('res-base').textContent = peso(laborBase);

        var chips = byId('res-chips');
        clearNode(chips);
        if (cMult > 1) addChip(chips, '\u00D7' + cMult.toFixed(2) + ' ' + complexity.replace(/_/g, ' '));
        if (uMult > 1) addChip(chips, '\u00D7' + uMult.toFixed(2) + ' ' + urgency);

        byId('res-labor').textContent = peso(labor);
        byId('res-mat-label').textContent = 'Materials (' + items + ' item' + (items === 1 ? '' : 's') + ')';
        byId('res-mat').textContent = peso(materialsTotal);
        byId('res-total').textContent = peso(total);
        byId('mat-subtotal').textContent = peso(materialsTotal);
        byId('mat-count').textContent = items + ' item' + (items === 1 ? '' : 's');
    }

    function addMaterialRow() {
        var tpl = byId('material-row-tpl');
        byId('materials-list').appendChild(tpl.content.cloneNode(true));
        recalc();
    }

    byId('add-material').addEventListener('click', addMaterialRow);

    byId('materials-list').addEventListener('click', function (ev) {
        var btn = ev.target && ev.target.closest ? ev.target.closest('.mat-remove') : null;
        if (!btn) return;
        var row = btn.closest('.mat-row');
        if (row && row.parentNode) {
            row.parentNode.removeChild(row);
            recalc();
        }
    });

    document.addEventListener('input', recalc);
    document.addEventListener('change', recalc);

    addMaterialRow();
})();
</script>
</body>
</html>
