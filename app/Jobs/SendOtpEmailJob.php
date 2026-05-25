<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendOtpEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public string $otpCode)
    {}

    public function handle(): void
    {
        Mail::send('emails.otp', ['code' => $this->otpCode], function ($message) {
            $message->to($this->user->email)
                    ->subject('Kode OTP Login LMS CodingMu MPR');
        });
    }
}