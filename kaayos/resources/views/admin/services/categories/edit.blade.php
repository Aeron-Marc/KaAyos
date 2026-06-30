@extends('layouts.admin')

@section('title', 'Edit Category')
@section('content')
<a href="{{ route('admin.service-categories.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Categories</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-pen"></i> Edit: {{ $category->name }}</h1>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.service-categories.update', $category) }}">
        @csrf @method('PUT')
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name <span style="color:var(--d10)">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug <span style="color:var(--d10)">*</span></label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" required>
                @error('slug') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3">{{ old('description', $category->description) }}</textarea>
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="icon">Icon (FontAwesome class name)</label>
            <input type="text" name="icon" id="icon" value="{{ old('icon', $category->icon) }}">
            @error('icon') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label class="toggle-label">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                Active
            </label>
        </div>
        <div class="page-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Update Category</button>
            <a href="{{ route('admin.service-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
