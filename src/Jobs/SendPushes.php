<?php

namespace jonlod\OneSignal\jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OneSignal;

class SendPushes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 5;

    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function retryUntil()
    {
        return now()->addMinute();
    }

    public function handle()
    {
        OneSignal::sendNotificationCustom($this->params);
    }
}
