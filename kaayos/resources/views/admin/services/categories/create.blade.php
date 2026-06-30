@extends('layouts.admin')

@section('title', 'Create Category')
@section('content')
<a href="{{ route('admin.service-categories.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Categories</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-plus"></i> Create Service Category</h1>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.service-categories.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name <span style="color:var(--d10)">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., Plumbing Services" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug <span style="color:var(--d10)">*</span></label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="e.g., plumbing-services" required>
                @error('slug') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" placeholder="Brief description of this category">{{ old('description') }}</textarea>
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="icon">Icon (FontAwesome class name, e.g., "wrench")</label>
            <input type="text" name="icon" id="icon" value="{{ old('icon') }}" placeholder="e.g., wrench, broom, bolt">
            @error('icon') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="page-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Category</button>
            <a href="{{ route('admin.service-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
