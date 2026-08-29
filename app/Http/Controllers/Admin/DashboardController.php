<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use App\Services\DashboardStatusService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardStatusService $status): View
    {
        return view('admin.dashboard', [
            'statusIndicators' => $status->indicators(),
            'newsCount' => News::count(),
            'userCount' => User::count(),
            'commentCount' => Comment::count(),
            'bannedCount' => User::where('is_banned', true)->count(),
            'latestNews' => News::with('user')->latest()->take(5)->get(),
            'recentNews' => News::with('user')->latest()->take(3)->get(),
        ]);
    }
}
