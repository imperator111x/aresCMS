<?php

namespace App\View\Composers;

use App\Services\CmsUpdateManager;
use Illuminate\View\View;

class CmsVersionComposer
{
    public function __construct(
        protected CmsUpdateManager $cmsUpdateManager
    ) {}

    public function compose(View $view): void
    {
        $view->with('cmsBundleVersion', $this->cmsUpdateManager->getInstalledVersion());
    }
}
