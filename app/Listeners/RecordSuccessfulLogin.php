<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class RecordSuccessfulLogin
{
    public function __construct(
        protected Request $request
    ) {}

    public function handle(Login $event): void
    {
        LoginHistory::recordSuccess($event->user, $this->request);
    }
}
