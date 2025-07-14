<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;

class TeacherAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct(User $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        $verificationUrl = url('/verify-email/' . $this->user->id . '/' . sha1($this->user->email));
        return $this->subject('Your Teacher Account Details')
            ->markdown('emails.teacher.created')
            ->with([
                'user' => $this->user,
                'password' => $this->password,
                'verificationUrl' => $verificationUrl,
            ]);
    }
} 