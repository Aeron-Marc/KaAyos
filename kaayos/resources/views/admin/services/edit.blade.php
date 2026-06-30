@extends('layouts.admin')

@section('title', 'Edit Service')
@section('content')
<a href="{{ route('admin.services.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Services</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-pen"></i> Edit: {{ $service->name }}</h1>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.services.update', $service) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="category_id">Category <span style="color:var(--d10)">*</span></label>
            <select name="category_id" id="category_id" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name <span style="color:var(--d10)">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $service->name) }}" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug <span style="color:var(--d10)">*</span></label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $service->slug) }}" required>
                @error('slug') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3">{{ old('description', $service->description) }}</textarea>
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="base_price">Base Price (₱)</label>
            <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $service->base_price) }}" step="0.01" min="0">
            @error('base_price') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label class="toggle-label">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                Active
            </label>
        </div>
        <div class="page-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Update Service</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
