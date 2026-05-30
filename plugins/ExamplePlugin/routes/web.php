<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/plugin-example', static function () {
        return response('Example plugin route is active.', 200);
    })->name('plugin.example.index');
});

