<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('portal.emails.team_invitation.title') }}</title>
    <style>
        body{font-family:Arial,sans-serif;line-height:1.6;color:#333;background:#f4f4f4;margin:0;padding:20px;}
        .container{max-width:600px;margin:0 auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
        .header{text-align:center;padding-bottom:20px;border-bottom:2px solid #667eea;}
        .header h1{color:#667eea;margin:0;}
        .content{padding:20px 0;}
        .credentials{background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0;}
        .credentials p{margin:5px 0;}
        .btn{display:inline-block;padding:12px 24px;background:#667eea;color:#fff;text-decoration:none;border-radius:5px;margin:20px 0;}
        .footer{text-align:center;padding-top:20px;border-top:1px solid #ddd;color:#666;font-size:12px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 {{ __('portal.emails.team_invitation.heading') }}</h1>
        </div>
        
        <div class="content">
            <p>{!! __('portal.emails.team_invitation.hi', ['name' => e($user->name)]) !!}</p>
            
            <p>{!! __('portal.emails.team_invitation.invited_as', ['role' => e(ucfirst($user->role->value))]) !!}</p>
            
            <p>{{ __('portal.emails.team_invitation.credentials_intro') }}</p>
            
            <div class="credentials">
                <p><strong>{{ __('portal.emails.labels.email') }}:</strong> {{ $user->email }}</p>
                <p><strong>{{ __('portal.emails.team_invitation.temporary_password') }}:</strong> <code style="background:#fff;padding:5px 10px;border-radius:3px;font-size:14px;">{{ $password }}</code></p>
            </div>
            
            <p><strong>⚠️ {{ __('portal.emails.team_invitation.important') }}:</strong> {{ __('portal.emails.team_invitation.change_password_note') }}</p>
            
            <a href="{{ url('/login') }}" class="btn">{{ __('portal.emails.team_invitation.login_cta') }}</a>
            
            <p>{{ __('portal.emails.team_invitation.questions') }}</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ __('portal.emails.team_invitation.footer_rights') }}</p>
            <p>{{ __('portal.emails.team_invitation.footer_no_reply') }}</p>
        </div>
    </div>
</body>
</html>

