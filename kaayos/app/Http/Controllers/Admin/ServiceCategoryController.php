<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::withCount('services')->latest()->paginate(20);
        return view('admin.services.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.services.categories.create');
    }

    public function store(StoreServiceCategoryRequest $request)
    {
        ServiceCategory::create([
            'name'        => $request->input('name'),
            'slug'        => Str::slug($request->input('slug')),
            'description' => $request->input('description'),
            'icon'        => $request->input('icon'),
        ]);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category created successfully.');
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        return view('admin.services.categories.edit', ['category' => $serviceCategory]);
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory)
    {
        $serviceCategory->update([
            'name'        => $request->input('name'),
            'slug'        => Str::slug($request->input('slug')),
            'description' => $request->input('description'),
            'icon'        => $request->input('icon'),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category updated successfully.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        if ($serviceCategory->services()->exists()) {
            return back()->with('error', 'Cannot delete a category that has services assigned to it.');
        }

        $serviceCategory->delete();

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category deleted successfully.');
    }
}
