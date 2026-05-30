<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsCategoryController extends Controller
{
    public function index()
    {
        $categories = NewsCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.news-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:news_categories,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $name = trim((string) $validated['name']);
        NewsCategory::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return back()->with('success', __('Category created successfully.'));
    }

    public function update(Request $request, NewsCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('news_categories', 'name')->ignore($category->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $oldName = (string) $category->name;
        $newName = trim((string) $validated['name']);
        $category->name = $newName;
        $category->sort_order = (int) ($validated['sort_order'] ?? 0);
        if ($oldName !== $newName) {
            $category->slug = $this->uniqueSlug($newName, $category->id);
        }
        $category->save();

        News::query()->where('category', $oldName)->update(['category' => $newName]);

        return back()->with('success', __('Category updated successfully.'));
    }

    public function destroy(NewsCategory $category)
    {
        News::query()->where('category', $category->name)->update(['category' => null]);
        $category->delete();

        return back()->with('success', __('Category deleted successfully.'));
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'category';
        }

        $slug = $base;
        $i = 2;
        while (NewsCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
