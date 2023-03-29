<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $method;
    /**
     * Create a new job instance.
     */
    public function __construct($method, $message)
    {
        //
        $this->message = $message;
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if($this->method == "login"){
            Log::channel('login')->info($this->message);
        }
        if($this->method == "register"){
            Log::channel('register')->info($this->message);
        }
        if($this->method == "store"){
            Log::channel('store')->info($this->message . " 存儲成功");
        }
        if($this->method == "update"){
            Log::channel('update')->info($this->message . " 更新成功");
        }
        if($this->method == "destroy"){
            Log::channel('destroy')->info($this->message . " 刪除成功");
        }
        if($this->method == "error") {
            Log::channel('errot')->error($this->message);
        }
    }
}
