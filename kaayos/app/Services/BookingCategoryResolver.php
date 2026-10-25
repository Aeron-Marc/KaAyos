<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Cache;

class BookingCategoryResolver
{
    /**
     * Resolve a free-text booking category to a canonical category label.
     */
    public function resolve(?string $raw): string
    {
        $raw = mb_strtolower(trim((string) $raw));

        if ($raw === '') {
            return __('admin.dash.categories.uncategorized');
        }

        $map = $this->map();

        if (array_key_exists($raw, $map['aliases'])) {
            $target = $map['aliases'][$raw];

            return $target ?: __('admin.dash.categories.uncategorized');
        }

        $label = $map['services'][$raw] ?? $map['categories'][$raw] ?? null;

        return $label ?: __('admin.dash.categories.uncategorized');
    }

    /**
     * @return array{aliases: array<string, string|null>, services: array<string, string>, categories: array<string, string>}
     */
    private function map(): array
    {
        return Cache::remember('admin.booking_category_map', now()->addHour(), function () {
            $aliases = collect(config('dashboard.category_aliases', []))
                ->mapWithKeys(fn ($target, $source) => [mb_strtolower((string) $source) => $target])
                ->all();

            $categoriesById = ServiceCategory::pluck('name', 'id');

            $categories = $categoriesById
                ->mapWithKeys(fn ($name) => [mb_strtolower((string) $name) => $name])
                ->all();

            $services = Service::query()
                ->get(['id', 'category_id', 'name'])
                ->mapWithKeys(fn ($service) => [
                    mb_strtolower((string) $service->name) => $categoriesById[$service->category_id] ?? null,
                ])
                ->filter()
                ->all();

            return [
                'aliases' => $aliases,
                'services' => $services,
                'categories' => $categories,
            ];
        });
    }
}
