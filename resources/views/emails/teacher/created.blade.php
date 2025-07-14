@component('mail::message')
# Welcome to the School Management System!

Hello {{ $user->name }},

Your teacher account has been created. Here are your login details:

- **Email:** {{ $user->email }}
- **Temporary Password:** {{ $password }}

@component('mail::button', ['url' => $verificationUrl])
Verify Your Email
@endcomponent

Please use the above credentials to log in. You will be prompted to set a new password after verifying your email.

If you did not expect this email, please ignore it.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 