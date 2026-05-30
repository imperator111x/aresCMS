<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Support\NewsContentSanitizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $news = News::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = NewsCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        return view('admin.news.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:120',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'published' => 'boolean',
            'comments_enabled' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except(['content', 'category', 'published', 'comments_enabled', 'published_at']);
        $data['content'] = NewsContentSanitizer::sanitize($request->input('content'));
        $data['user_id'] = auth()->id();
        $data['category'] = $request->filled('category') ? trim((string) $request->category) : null;

        $published = $request->boolean('published');
        $data['published'] = $published;
        $data['comments_enabled'] = $request->has('comments_enabled');
        if ($published) {
            $data['published_at'] = $request->filled('published_at')
                ? Carbon::parse($request->input('published_at'))
                : now();
        } else {
            $data['published_at'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        if (! Schema::hasColumn('news', 'category')) {
            unset($data['category']);
        }
        if (! Schema::hasColumn('news', 'published_at')) {
            unset($data['published_at']);
        }
        if (! Schema::hasColumn('news', 'comments_enabled')) {
            unset($data['comments_enabled']);
        }

        News::create($data);

        return redirect()->route('admin.news.index')
            ->with('success', __('News created successfully!'));
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        $news->load([
            'rootComments.user',
            'rootComments.replies.user',
        ]);

        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        $categories = NewsCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        return view('admin.news.edit', compact('news', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:120',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'published' => 'boolean',
            'comments_enabled' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except(['content', 'category', 'published', 'comments_enabled', 'published_at']);
        $data['content'] = NewsContentSanitizer::sanitize($request->input('content'));
        $data['category'] = $request->filled('category') ? trim((string) $request->category) : null;

        $published = $request->boolean('published');
        $data['published'] = $published;
        $data['comments_enabled'] = $request->has('comments_enabled');
        if ($published) {
            $data['published_at'] = $request->filled('published_at')
                ? Carbon::parse($request->input('published_at'))
                : ($news->published_at ?? now());
        } else {
            $data['published_at'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        if (! Schema::hasColumn('news', 'category')) {
            unset($data['category']);
        }
        if (! Schema::hasColumn('news', 'published_at')) {
            unset($data['published_at']);
        }
        if (! Schema::hasColumn('news', 'comments_enabled')) {
            unset($data['comments_enabled']);
        }

        $news->update($data);

        return redirect()->route('admin.news.index')
            ->with('success', __('News updated successfully!'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', __('News deleted successfully!'));
    }
}
