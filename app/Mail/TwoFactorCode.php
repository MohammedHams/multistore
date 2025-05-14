<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorCode extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The authentication code.
     *
     * @var string
     */
    public $code;

    /**
     * Create a new message instance.
     *
     * @param  string  $code
     * @return void
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('رمز التحقق بخطوتين')
                    ->markdown('emails.auth.two-factor-code');
    }
}
