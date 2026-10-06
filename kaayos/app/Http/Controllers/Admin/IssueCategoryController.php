<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIssueCategoryRequest;
use App\Http\Requests\Admin\UpdateIssueCategoryRequest;
use App\Models\Booking;
use App\Models\IssueCategory;
use Illuminate\Support\Str;

class IssueCategoryController extends Controller
{
    public function index()
    {
        $categories = IssueCategory::withCount('bookings')->latest()->paginate(20);
        return view('admin.issue-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.issue-categories.create');
    }

    public function store(StoreIssueCategoryRequest $request)
    {
        IssueCategory::create([
            'name'             => $request->input('name'),
            'slug'             => Str::slug($request->input('slug')),
            'description'      => $request->input('description'),
            'icon'             => $request->input('icon'),
            'service_category' => $request->input('service_category') ?: null,
        ]);

        return redirect()->route('admin.issue-categories.index')
            ->with('success', 'Issue category created successfully.');
    }

    public function edit(IssueCategory $issueCategory)
    {
        return view('admin.issue-categories.edit', ['category' => $issueCategory]);
    }

    public function update(UpdateIssueCategoryRequest $request, IssueCategory $issueCategory)
    {
        $issueCategory->update([
            'name'             => $request->input('name'),
            'slug'             => Str::slug($request->input('slug')),
            'description'      => $request->input('description'),
            'icon'             => $request->input('icon'),
            'service_category' => $request->input('service_category') ?: null,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.issue-categories.index')
            ->with('success', 'Issue category updated successfully.');
    }

    public function destroy(IssueCategory $issueCategory)
    {
        if (Booking::where('issue_category_id', $issueCategory->id)->exists()) {
            return back()->with('error', 'Cannot delete a category that is used by bookings.');
        }

        $issueCategory->delete();

        return redirect()->route('admin.issue-categories.index')
            ->with('success', 'Issue category deleted successfully.');
    }
}
