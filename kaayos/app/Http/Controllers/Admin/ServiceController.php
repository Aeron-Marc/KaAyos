<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('category');

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $services = $query->latest()->paginate(20)->withQueryString();
        $categories = ServiceCategory::active()->get();

        return view('admin.services.index', compact('services', 'categories'));
    }

    public function create()
    {
        $categories = ServiceCategory::active()->get();
        return view('admin.services.create', compact('categories'));
    }

    public function store(StoreServiceRequest $request)
    {
        Service::create([
            'category_id' => $request->input('category_id'),
            'name'        => $request->input('name'),
            'slug'        => Str::slug($request->input('slug')),
            'description' => $request->input('description'),
            'base_price'  => $request->input('base_price'),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::active()->get();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update([
            'category_id' => $request->input('category_id'),
            'name'        => $request->input('name'),
            'slug'        => Str::slug($request->input('slug')),
            'description' => $request->input('description'),
            'base_price'  => $request->input('base_price'),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
