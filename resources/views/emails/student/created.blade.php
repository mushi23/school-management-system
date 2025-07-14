@component('mail::message')
# 🎓 Welcome to {{ config('app.name') }}

Hello {{ $user->name }},

Your student account has been successfully created. Below are your login credentials:

@component('mail::panel')
**Email:** {{ $user->email }}
**Temporary Password:** {{ $password }}
@endcomponent

> ⚠️ **Important:** You will be required to change your password after your first login.

You can log in to your account using the link below:

@component('mail::button', ['url' => url('/login')])
Login to Student Portal
@endcomponent

@if(isset($verificationUrl))
---

@component('mail::panel')
**Please verify your email address to activate your account.**
@endcomponent

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent
@endif

If you did not request this account or believe this is a mistake, please contact the school administrator immediately.

Thanks,
The {{ config('app.name') }} Team
@endcomponent
