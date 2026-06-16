<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #10b981 0%, #2C3F43 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f0fdf4; padding: 30px; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">âœ… {{ __('portal.emails.complaint_resolved.subject') }}</h1>
        </div>
        <div class="content">
            <p>{{ __('portal.emails.greeting', ['name' => $complaint->client->name]) }},</p>
            
            <p>{!! __('portal.emails.complaint_resolved.intro', ['project' => e($complaint->project->title)]) !!}</p>

            <h4>{{ __('portal.emails.complaint_resolved.original') }}:</h4>
            <p style="background: white; padding: 15px; border-left: 4px solid #94a3b8;">"{{ $complaint->complaint }}"</p>

            <h4>{{ __('portal.emails.complaint_resolved.resolution') }}:</h4>
            <p style="background: white; padding: 15px; border-left: 4px solid #10b981;">{{ $complaint->resolution_note }}</p>

            <p>{{ __('portal.emails.complaint_resolved.resolved_by', ['name' => $complaint->resolvedBy->name, 'date' => $complaint->resolved_at->translatedFormat('F j, Y')]) }}</p>

            <p style="margin-top: 30px;">
                <a href="{{ route('projects.client.show', $complaint->project) }}" 
                   style="display: inline-block; background: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    {{ __('portal.emails.view_project') }}
                </a>
            </p>
        </div>
        <div class="footer">
            <p>{{ __('portal.emails.complaint_resolved.footer') }}</p>
        </div>
    </div>
</body>
</html>

