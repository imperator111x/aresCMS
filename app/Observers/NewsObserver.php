<?php

namespace App\Observers;

use App\Models\News;
use App\Support\ActivityLogger;

class NewsObserver
{
    public function created(News $news): void
    {
        ActivityLogger::log(
            'news.created',
            $news->title,
            $news,
            ['title' => $news->title, 'published' => (bool) $news->published]
        );
    }

    public function updated(News $news): void
    {
        $changes = array_diff_assoc($news->getChanges(), array_flip(['updated_at']));
        unset($changes['content']);
        if (isset($news->getChanges()['content'])) {
            $changes['content_changed'] = true;
        }

        ActivityLogger::log(
            'news.updated',
            $news->title,
            $news,
            ['changes' => $changes]
        );
    }

    public function deleted(News $news): void
    {
        ActivityLogger::log(
            'news.deleted',
            $news->title,
            $news,
            ['title' => $news->title]
        );
    }
}
