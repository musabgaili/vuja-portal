<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #10b981 0%, #2C3F43 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">ðŸŽ‰ {{ __('portal.emails.project_completed.title') }}</h1>
        </div>
        <div class="content">
            <p><strong>{{ __('portal.emails.project_completed.congrats', ['name' => $client->name]) }}</strong></p>
            
            <p>{!! __('portal.emails.project_completed.intro', ['project' => e($project->title)]) !!}</p>

            <p>{{ __('portal.emails.project_completed.body') }}</p>

            <p style="margin-top: 30px;">
                <a href="{{ route('projects.client.show', $project) }}" 
                   style="display: inline-block; background: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    {{ __('portal.emails.view_project') }}
                </a>
            </p>
        </div>
        <div class="footer">
            <p>{{ __('portal.emails.project_completed.footer') }}</p>
        </div>
    </div>
</body>
</html>

