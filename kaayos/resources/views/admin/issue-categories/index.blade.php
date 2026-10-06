@extends('layouts.admin')

@section('title', 'Issue Categories')
@section('content')
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-list-check"></i> Issue Categories</h1>
        <p>Manage issue types clients choose when posting a job</p>
    </div>
    <div class="header-right">
        <a href="{{ route('admin.issue-categories.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Issue Category</a>
    </div>
</div>

<div class="table-container">
    @if($categories->count())
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Trade</th>
                    <th>Bookings</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            @if($cat->icon)<i class="fa-solid {{ $cat->icon }}" style="color:var(--b6);font-size:1.2rem"></i>@endif
                            <span class="fw-600">{{ $cat->name }}</span>
                        </div>
                    </td>
                    <td class="text-sm text-muted">{{ $cat->slug }}</td>
                    <td>
                        @if($cat->service_category)
                            <span class="status-badge" style="background:var(--g0);color:var(--g7)">{{ \Illuminate\Support\Str::ucfirst($cat->service_category) }}</span>
                        @else
                            <span class="status-badge" style="background:var(--b0);color:var(--b7)">All trades</span>
                        @endif
                    </td>
                    <td><span class="status-badge" style="background:var(--b0);color:var(--b7)">{{ $cat->bookings_count }}</span></td>
                    <td>
                        @if($cat->is_active)
                            <span class="status-badge status-active"><i class="fa-solid fa-check-circle"></i> Active</span>
                        @else
                            <span class="status-badge status-suspended"><i class="fa-solid fa-eye-slash"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="text-sm text-muted">{{ $cat->created_at->format('M d, Y') }}</td>
                    <td style="text-align: center;">
                        <div class="actions-cell" style="justify-content: center;">
                            <a href="{{ route('admin.issue-categories.edit', $cat) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.issue-categories.destroy', $cat) }}" style="display:inline" onsubmit="return confirm('Delete this issue category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $categories->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-list-check"></i></div>
            <div class="empty-state-title">No issue categories yet</div>
            <div class="empty-state-subtitle"><a href="{{ route('admin.issue-categories.create') }}" style="color:var(--b6)">Create your first issue category</a></div>
        </div>
    @endif
</div>
@endsection
