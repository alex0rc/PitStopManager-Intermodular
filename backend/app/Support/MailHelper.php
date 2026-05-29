<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    public static function sendSafely(string $to, object $mailable, array $context = []): void
    {
        if ($to === '') {
            return;
        }

        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Failed to send email', array_merge($context, [
                'mailable' => $mailable::class,
                'error'    => $e->getMessage(),
            ]));
        }
    }
}
