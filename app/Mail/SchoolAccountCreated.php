<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class SchoolAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tempPassword;
    

    public function __construct($user, $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
    }

    public function build()
    {
        $verificationLink = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );

        return $this->subject('🎓 Your School Admin Account is Ready')
                    ->markdown('emails.school_created')
                    ->with([
                        'userName' => $this->user->name,          // match {{ $userName }}
                        'email' => $this->user->email,            // match {{ $email }}
                        'temporaryPassword' => $this->tempPassword, // match {{ $temporaryPassword }}
                        'verificationLink' => $verificationLink,
                    ]);
    }
}

