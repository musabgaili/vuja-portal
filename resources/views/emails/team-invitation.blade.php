<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to VujaDe Platform</title>
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
            <h1>🎉 Welcome to VujaDe Platform!</h1>
        </div>
        
        <div class="content">
            <p>Hi <strong>{{ $user->name }}</strong>,</p>
            
            <p>You've been invited to join the VujaDe Platform as a <strong>{{ ucfirst($user->role->value) }}</strong>.</p>
            
            <p>Your account has been created with the following credentials:</p>
            
            <div class="credentials">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Temporary Password:</strong> <code style="background:#fff;padding:5px 10px;border-radius:3px;font-size:14px;">{{ $password }}</code></p>
            </div>
            
            <p><strong>⚠️ Important:</strong> Please change your password after your first login for security purposes.</p>
            
            <a href="{{ url('/login') }}" class="btn">Login to Your Account</a>
            
            <p>If you have any questions, please contact your administrator.</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} VujaDe Platform. All rights reserved.</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>

