<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
        .container{max-width:600px;margin:0 auto;padding:20px;}
        .footer{text-align:center;padding:20px;color:#6b7280;font-size:12px;}
    </style>
</head>
<body>
    <div class="container">
        <div style="background:linear-gradient(135deg,#10b981 0%,#1C575F 100%);color:white;padding:30px;text-align:center;border-radius:8px 8px 0 0;">
            <h1 style="margin:0;">✨ Milestone Completed!</h1>
        </div>
        <div style="background:#f0fdf4;padding:30px;border-radius:0 0 8px 8px;">
            <p><strong>Great news!</strong> A milestone in your project has been completed and is ready for your review.</p>
            
            <h3 style="color:#10b981;">Milestone Details:</h3>
            <ul>
                <li><strong>Title:</strong> {{ $milestone->title }}</li>
                <li><strong>Project:</strong> {{ $milestone->project->title }}</li>
                <li><strong>Completed:</strong> {{ $milestone->completed_at->format('F j, Y \a\t g:i A') }}</li>
                <li><strong>Progress:</strong> {{ $milestone->completion_percentage }}%</li>
            </ul>

            @if($milestone->description)
            <h4>Description:</h4>
            <p style="background:white;padding:15px;border-left:4px solid #10b981;">{{ $milestone->description }}</p>
            @endif

            <div style="background:white;padding:20px;border-radius:8px;margin:20px 0;border:2px solid #10b981;">
                <h4 style="margin-top:0;">📋 Ready for Your Review</h4>
                <p>Please log in to your dashboard to review this milestone and provide your approval or feedback.</p>
            </div>

            <p style="margin-top:30px;">
                <a href="{{ route('projects.client.show', $milestone->project) }}" 
                   style="display:inline-block;background:#10b981;color:white;padding:12px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">
                    Review Milestone
                </a>
            </p>
        </div>
        <div class="footer">
            <p>VujaDe Platform Notification</p>
        </div>
    </div>
</body>
</html>

