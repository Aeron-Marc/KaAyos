@extends('layouts.worker')

@section('title', 'My Services')
@section('page_title', 'My Services')

@section('skeleton')
    <div class="sp-panel">
        <div class="skeleton skeleton-title" style="width:200px;margin-bottom:16px;"></div>
        <div style="display:flex;align-items:center;gap:16px;padding:18px 22px;border-bottom:1px solid var(--g1);">
            <div style="flex:1;"><div class="skeleton skeleton-text" style="width:50%;"></div><div class="skeleton skeleton-text-sm" style="width:70%;"></div></div>
            <div class="skeleton skeleton-badge"></div>
        </div>
        <div style="display:flex;align-items:center;gap:16px;padding:18px 22px;">
            <div style="flex:1;"><div class="skeleton skeleton-text" style="width:45%;"></div><div class="skeleton skeleton-text-sm" style="width:60%;"></div></div>
            <div class="skeleton skeleton-badge"></div>
        </div>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div style="background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.88rem;display:flex;align-items:center;gap:8px;">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fef2f2;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.88rem;">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="welcome-banner">
    <h2>My Services</h2>
    <p>Manage the services you offer. Set your custom prices or use the default rates. Clients will see these services on your profile and can book them directly.</p>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Services</div>
            <h2 class="section-title">Active Services</h2>
        </div>
        <span style="font-size:.82rem;color:var(--g4);font-weight:500;">
            {{ $workerServices->count() }} service{{ $workerServices->count() !== 1 ? 's' : '' }} linked
        </span>
    </div>

    @forelse($workerServices as $ps)
        <div style="display:flex;align-items:center;gap:16px;padding:18px 22px;border-bottom:1px solid var(--g1);transition:background .15s;" onmouseover="this.style.background='var(--off)'" onmouseout="this.style.background=''">
            <div style="width:44px;height:44px;border-radius:10px;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;">
                <i class="fa-solid fa-wrench" aria-hidden="true"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.9rem;font-weight:600;color:var(--b9);">{{ $ps->service->name }}</div>
                <div style="font-size:.82rem;color:var(--g4);margin-top:2px;">
                    {{ $ps->service->category->name ?? 'General' }}
                    @if($ps->service->base_price)
                        &middot; Default: ₱{{ number_format($ps->service->base_price, 2) }}
                    @endif
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
                {{-- Price editor --}}
                <form action="{{ route('worker.services.price', $ps->service_id) }}" method="POST" style="display:flex;align-items:center;gap:6px;">
                    @csrf @method('PUT')
                    <span style="font-size:.85rem;color:var(--g6);">₱</span>
                    <input type="number" name="custom_price" value="{{ $ps->custom_price ?? '' }}" step="0.01" min="0"
                           placeholder="{{ $ps->service->base_price ?? '0' }}"
                           style="width:90px;padding:5px 8px;border:1.5px solid var(--g2);border-radius:6px;font-size:.85rem;background:var(--off);text-align:right;">
                    <button type="submit" style="background:var(--b0);color:var(--b6);border:none;width:30px;height:30px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem;" title="Save price">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                    </button>
                </form>

                {{-- Availability toggle --}}
                <form action="{{ route('worker.services.toggle', $ps->service_id) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit" title="{{ $ps->is_available ? 'Available' : 'Hidden' }}"
                            style="border:none;width:36px;height:20px;border-radius:10px;cursor:pointer;position:relative;transition:background .2s;{{ $ps->is_available ? 'background:#22c55e;' : 'background:var(--g3);' }}">
                        <span style="position:absolute;top:2px;{{ $ps->is_available ? 'right:2px;' : 'left:2px;' }}width:16px;height:16px;border-radius:50%;background:#fff;transition:left .2s,right .2s;box-shadow:0 1px 3px rgba(0,0,0,.15);"></span>
                    </button>
                </form>

                {{-- Remove --}}
                <form action="{{ route('worker.services.remove', $ps->service_id) }}" method="POST"
                      onsubmit="return confirm('Remove {{ $ps->service->name }} from your services?');">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:none;color:var(--g4);cursor:pointer;padding:4px;font-size:.9rem;" title="Remove service">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="text-align:center;padding:48px 20px;color:var(--g4);">
            <i class="fa-solid fa-list-check" style="font-size:2.5rem;display:block;margin-bottom:12px;" aria-hidden="true"></i>
            <p style="font-size:.9rem;">No services linked yet.</p>
            <p style="font-size:.82rem;margin-top:4px;">Add services below so clients can see what you offer and book you directly.</p>
        </div>
    @endforelse
</div>

@if($availableServices->count() > 0)
<div class="card-panel" style="margin-top:16px;">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Add Service</div>
            <h2 class="section-title">Available Services</h2>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;padding:18px 22px;">
        @foreach($availableServices as $svc)
            <div style="background:var(--off);border:1px solid var(--g1);border-radius:10px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                <div style="min-width:0;">
                    <div style="font-size:.85rem;font-weight:600;color:var(--b9);">{{ $svc->name }}</div>
                    <div style="font-size:.78rem;color:var(--g4);">₱{{ number_format($svc->base_price ?? 0, 2) }}</div>
                </div>
                <form action="{{ route('worker.services.add') }}" method="POST" style="flex-shrink:0;">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $svc->id }}">
                    <button type="submit" style="background:var(--b6);color:#fff;border:none;padding:5px 12px;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> Add
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endif

@endsection
