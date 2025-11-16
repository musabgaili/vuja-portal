<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
        .container{max-width:600px;margin:0 auto;padding:20px;}
        .header{background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:white;padding:30px;text-align:center;border-radius:8px 8px 0 0;}
        .content{background:#f0fdf4;padding:30px;border-radius:0 0 8px 8px;}
        .footer{text-align:center;padding:20px;color:#6b7280;font-size:12px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">✅ Request Resolved</h1>
        </div>
        <div class="content">
            <p>Dear {{ $projectRequest->client->name }},</p>
            
            <p>Your request regarding <strong>{{ $projectRequest->project->title }}</strong> has been addressed.</p>

            <h4>Your Request:</h4>
            <p style="background:white;padding:15px;border-left:4px solid #94a3b8;">"{{ $projectRequest->request }}"</p>

            <h4>Response:</h4>
            <p style="background:white;padding:15px;border-left:4px solid #10b981;">{{ $projectRequest->response }}</p>

            <p>Handled by {{ $projectRequest->handledBy->name }} on {{ $projectRequest->handled_at->format('F j, Y') }}</p>

            <p style="margin-top:30px;">
                <a href="{{ route('projects.client.show', $projectRequest->project) }}" 
                   style="display:inline-block;background:#10b981;color:white;padding:12px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">
                    View Project
                </a>
            </p>
        </div>
        <div class="footer">
            <p>Thank you for working with us</p>
        </div>
    </div>
</body>
</html>

