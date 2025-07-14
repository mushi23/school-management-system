<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;


class StudentAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $temporaryPassword;

    public function __construct(User $user, $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;

        $this->verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->email)]
        );
    }

    public function build()
    {
        return $this->subject('Your Student Account Details')
            ->markdown('emails.student.created')
            ->with([
                'user' => $this->user,
                'password' => $this->tempPassword,
                'verificationUrl' => $this->verificationUrl,
            ]);
    }
}
