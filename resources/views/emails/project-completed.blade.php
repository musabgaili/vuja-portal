<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">🎉 Project Completed!</h1>
        </div>
        <div class="content">
            <p><strong>Congratulations, {{ $client->name }}!</strong></p>
            
            <p>Your project <strong>"{{ $project->title }}"</strong> has been successfully completed.</p>

            <p>Thank you for confirming receipt of all deliverables. We hope you're satisfied with the results!</p>

            <p style="margin-top: 30px;">
                <a href="{{ route('projects.client.show', $project) }}" 
                   style="display: inline-block; background: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    View Project
                </a>
            </p>
        </div>
        <div class="footer">
            <p>VujaDe Platform - Thank you for your business!</p>
        </div>
    </div>
</body>
</html>

