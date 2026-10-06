@extends('layouts.admin')

@section('title', 'Create Issue Category')
@section('content')
<a href="{{ route('admin.issue-categories.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Issue Categories</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-plus"></i> Create Issue Category</h1>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.issue-categories.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label for="name">Name <span style="color:var(--d10)">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., Plumbing Leak" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug <span style="color:var(--d10)">*</span></label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="e.g., plumbing-leak" required>
                @error('slug') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" placeholder="Brief description of this issue type">{{ old('description') }}</textarea>
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="icon">Icon (FontAwesome class name, e.g., "droplet")</label>
            <input type="text" name="icon" id="icon" value="{{ old('icon') }}" placeholder="e.g., droplet, bolt, bug">
            @error('icon') <div class="error">{{ $message }}</div> @enderror
        </div>
        @php($tradeLabels = ['plumbing' => 'Plumbing', 'electrical' => 'Electrical', 'carpentry' => 'Carpentry', 'painting' => 'Painting', 'aircon' => 'Aircon Services', 'cleaning' => 'Cleaning', 'roofing' => 'Roofing', 'welding' => 'Welding', 'gardening' => 'Gardening', 'other' => 'Other'])
        <div class="form-group">
            <label for="service_category">Trade</label>
            <select name="service_category" id="service_category">
                <option value="">All trades</option>
                @foreach($tradeLabels as $trade => $tradeLabel)
                    <option value="{{ $trade }}" {{ old('service_category') === $trade ? 'selected' : '' }}>{{ $tradeLabel }}</option>
                @endforeach
            </select>
            <small style="font-size:.74rem;color:var(--g4)">Shown only to clients booking a worker of this trade. "All trades" appears for every worker.</small>
            @error('service_category') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="page-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Issue Category</button>
            <a href="{{ route('admin.issue-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
