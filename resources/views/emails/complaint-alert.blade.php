<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fef2f2; padding: 30px; border-radius: 0 0 8px 8px; border: 2px solid #dc2626; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">🚨 URGENT: Client Complaint</h1>
        </div>
        <div class="content">
            <p><strong>A client has submitted a complaint that requires immediate attention.</strong></p>
            
            <h3 style="color: #dc2626;">Complaint Details:</h3>
            <ul>
                <li><strong>Project:</strong> {{ $complaint->project->title }}</li>
                <li><strong>Client:</strong> {{ $complaint->client->name }}</li>
                <li><strong>Subject:</strong> {{ $complaint->subject }}</li>
                <li><strong>Submitted:</strong> {{ $complaint->created_at->format('F j, Y \a\t g:i A') }}</li>
            </ul>

            <h4>Complaint:</h4>
            <p style="background: white; padding: 15px; border-left: 4px solid #dc2626;">{{ $complaint->complaint }}</p>

            <p style="margin-top: 30px;">
                <a href="{{ route('projects.manager.show', $complaint->project) }}" 
                   style="display: inline-block; background: #dc2626; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    View Project & Respond
                </a>
            </p>
        </div>
        <div class="footer">
            <p>⚠️ This is a high-priority notification</p>
        </div>
    </div>
</body>
</html>

