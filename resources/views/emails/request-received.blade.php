<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
        .container{max-width:600px;margin:0 auto;padding:20px;}
        .header{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:white;padding:30px;text-align:center;border-radius:8px 8px 0 0;}
        .content{background:#f0f9ff;padding:30px;border-radius:0 0 8px 8px;border:2px solid #3b82f6;}
        .footer{text-align:center;padding:20px;color:#6b7280;font-size:12px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">📋 New Client Request</h1>
        </div>
        <div class="content">
            <p><strong>A client has submitted a new request for your attention.</strong></p>
            
            <h3 style="color:#2563eb;">Request Details:</h3>
            <ul>
                <li><strong>Project:</strong> {{ $projectRequest->project->title }}</li>
                <li><strong>Client:</strong> {{ $projectRequest->client->name }}</li>
                <li><strong>Subject:</strong> {{ $projectRequest->subject }}</li>
                <li><strong>Submitted:</strong> {{ $projectRequest->created_at->format('F j, Y \a\t g:i A') }}</li>
            </ul>

            <h4>Request:</h4>
            <p style="background:white;padding:15px;border-left:4px solid #3b82f6;">{{ $projectRequest->request }}</p>

            <p style="margin-top:30px;">
                <a href="{{ route('projects.manager.show', $projectRequest->project) }}" 
                   style="display:inline-block;background:#3b82f6;color:white;padding:12px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">
                    View Project & Respond
                </a>
            </p>
        </div>
        <div class="footer">
            <p>This is a client request notification</p>
        </div>
    </div>
</body>
</html>

