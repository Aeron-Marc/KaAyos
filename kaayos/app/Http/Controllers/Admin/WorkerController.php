<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'worker')
            ->with(['workerProfile', 'providerServices.service.category']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('providerServices.service', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'suspended') {
                $query->suspended();
            } elseif ($status === 'active') {
                $query->active();
            }
        }

        $workers = $query->latest()->paginate(20)->withQueryString();
        $categories = ServiceCategory::active()->get();

        return view('admin.workers.index', compact('workers', 'categories'));
    }
}
