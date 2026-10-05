@extends('layouts.admin')

@section('title', 'Create Service')
@section('content')
<a href="{{ route('admin.services.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Services</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-plus"></i> Create Service</h1>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.services.store') }}">
        @csrf
        <div class="form-group">
            <label for="category_id">Category <span style="color:var(--d10)">*</span></label>
            <select name="category_id" id="category_id" required>
                <option value="">Select a category</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name <span style="color:var(--d10)">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., Pipe Repair" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug <span style="color:var(--d10)">*</span></label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="e.g., pipe-repair" required>
                @error('slug') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" placeholder="Describe this service">{{ old('description') }}</textarea>
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="base_price">Minimum Base Rate / Floor (₱) <span style="color:var(--d10)">*</span></label>
            <input type="number" name="base_price" id="base_price" value="{{ old('base_price', '0.00') }}" step="0.01" min="0" placeholder="0.00" required>
            @error('base_price') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="billing_type">Billing Type</label>
            <select name="billing_type" id="billing_type">
                <option value="either" {{ old('billing_type', 'either') == 'either' ? 'selected' : '' }}>Fixed or hourly (client chooses)</option>
                <option value="fixed" {{ old('billing_type') == 'fixed' ? 'selected' : '' }}>Fixed price</option>
                <option value="hourly" {{ old('billing_type') == 'hourly' ? 'selected' : '' }}>Hourly</option>
            </select>
            <div style="font-size:.78rem;color:#64748b;margin-top:4px;">Sets the default billing mode when a client books this service.</div>
            @error('billing_type') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="page-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Service</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
