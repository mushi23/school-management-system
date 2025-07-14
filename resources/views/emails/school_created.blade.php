@component('mail::message')

Hello {{ $userName }},

Your school admin account has been created.

You can login with the following credentials:

**Email:** {{ $email }}
**Temporary Password:** {{ $temporaryPassword }}

Please **verify your email address** to activate your account by clicking the verification link below.

@component('mail::button', ['url' => $verificationLink])
Verify Your Email
@endcomponent

If you did not expect this email, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
