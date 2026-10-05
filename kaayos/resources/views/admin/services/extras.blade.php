@extends('layouts.admin')

@section('title', 'Service Extras - ' . $service->name)
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-list-check"></i> Extras Catalog: {{ $service->name }}</h1>
        <p>Manage standard add-ons and material pricing for this service</p>
    </div>
    <div class="header-right">
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Services</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
    <div class="table-container">
        @if($extras->count())
            <table>
                <thead>
                    <tr>
                        <th>Extra Name</th>
                        <th>Suggested Cost</th>
                        <th>Status</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($extras as $extra)
                    <tr>
                        <td class="fw-600">{{ $extra->name }}</td>
                        <td class="table-col-price">₱{{ number_format((float)$extra->suggested_cost, 2) }}</td>
                        <td>
                            @if($extra->is_active)
                                <span class="status-badge status-active"><i class="fa-solid fa-check-circle"></i> Active</span>
                            @else
                                <span class="status-badge status-suspended"><i class="fa-solid fa-eye-slash"></i> Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="actions-cell" style="justify-content: center;">
                                <form method="POST" action="{{ route('admin.services.extras.destroy', [$service, $extra]) }}" style="display:inline" onsubmit="return confirm('Delete this extra add-on?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">{{ $extras->links() }}</div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-box-open"></i></div>
                <div class="empty-state-title">No extras in catalog yet</div>
                <div class="empty-state-subtitle">Add standard add-ons or materials using the form on the right.</div>
            </div>
        @endif
    </div>

    <div class="panel" style="background: var(--bg-card, #fff); border-radius: 12px; padding: 20px; border: 1px solid var(--border-color, #e5e7eb);">
        <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 14px;"><i class="fa-solid fa-plus-circle"></i> Add New Extra</h3>
        <form method="POST" action="{{ route('admin.services.extras.store', $service) }}">
            @csrf
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="name" style="display: block; font-weight: 500; font-size: .85rem; margin-bottom: 5px;">Extra Name / Material</label>
                <input type="text" name="name" id="name" required placeholder="e.g. PVC pipe 3m, Extra wire" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="suggested_cost" style="display: block; font-weight: 500; font-size: .85rem; margin-bottom: 5px;">Suggested Cost (₱)</label>
                <input type="number" step="0.01" min="0" name="suggested_cost" id="suggested_cost" required placeholder="0.00" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div class="form-group" style="margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                <label for="is_active" style="font-size: .85rem; font-weight: 500; cursor: pointer;">Active in catalog</label>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-save"></i> Save Extra</button>
        </form>
    </div>
</div>
@endsection
