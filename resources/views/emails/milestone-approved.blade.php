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
        @if($milestone->client_approved)
        <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:white;padding:30px;text-align:center;border-radius:8px 8px 0 0;">
            <h1 style="margin:0;">✅ Milestone Approved!</h1>
        </div>
        <div style="background:#f0fdf4;padding:30px;border-radius:0 0 8px 8px;">
            <p><strong>Great news!</strong> The client has approved a milestone.</p>
        @else
        <div style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);color:white;padding:30px;text-align:center;border-radius:8px 8px 0 0;">
            <h1 style="margin:0;">❌ Milestone Rejected</h1>
        </div>
        <div style="background:#fef2f2;padding:30px;border-radius:0 0 8px 8px;">
            <p><strong>Attention:</strong> The client has rejected a milestone.</p>
        @endif
            
            <h3 style="color:{{ $milestone->client_approved ? '#10b981' : '#ef4444' }};">Details:</h3>
            <ul>
                <li><strong>Milestone:</strong> {{ $milestone->title }}</li>
                <li><strong>Project:</strong> {{ $milestone->project->title }}</li>
                <li><strong>Client:</strong> {{ $approver->name }}</li>
                <li><strong>Date:</strong> {{ $milestone->client_approved_at->format('F j, Y \a\t g:i A') }}</li>
            </ul>

            @if($milestone->approval_note)
            <h4>Client's {{ $milestone->client_approved ? 'Feedback' : 'Rejection Reason' }}:</h4>
            <p style="background:white;padding:15px;border-left:4px solid {{ $milestone->client_approved ? '#10b981' : '#ef4444' }};font-style:italic;">"{{ $milestone->approval_note }}"</p>
            @endif

            <p style="margin-top:30px;">
                <a href="{{ route('projects.manager.show', $milestone->project) }}" 
                   style="display:inline-block;background:{{ $milestone->client_approved ? '#10b981' : '#ef4444' }};color:white;padding:12px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">
                    View Project
                </a>
            </p>
        </div>
        <div class="footer">
            <p>VujaDe Platform Notification</p>
        </div>
    </div>
</body>
</html>
