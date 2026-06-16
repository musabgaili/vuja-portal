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
        <div style="background:linear-gradient(135deg,#10b981 0%,#2C3F43 100%);color:white;padding:30px;text-align:center;border-radius:8px 8px 0 0;">
            <h1 style="margin:0;">âœ… {{ __('portal.emails.milestone_approved.approved_title') }}</h1>
        </div>
        <div style="background:#f0fdf4;padding:30px;border-radius:0 0 8px 8px;">
            <p><strong>{{ __('portal.emails.milestone_approved.great_news') }}</strong> {{ __('portal.emails.milestone_approved.approved_intro') }}</p>
        @else
        <div style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);color:white;padding:30px;text-align:center;border-radius:8px 8px 0 0;">
            <h1 style="margin:0;">âŒ {{ __('portal.emails.milestone_approved.rejected_title') }}</h1>
        </div>
        <div style="background:#fef2f2;padding:30px;border-radius:0 0 8px 8px;">
            <p><strong>{{ __('portal.emails.milestone_approved.attention') }}</strong> {{ __('portal.emails.milestone_approved.rejected_intro') }}</p>
        @endif
            
            <h3 style="color:{{ $milestone->client_approved ? '#10b981' : '#ef4444' }};">{{ __('portal.emails.labels.details') }}:</h3>
            <ul>
                <li><strong>{{ __('portal.emails.labels.milestone') }}:</strong> {{ $milestone->title }}</li>
                <li><strong>{{ __('portal.emails.labels.project') }}:</strong> {{ $milestone->project->title }}</li>
                <li><strong>{{ __('portal.emails.labels.client') }}:</strong> {{ $approver->name }}</li>
                <li><strong>{{ __('portal.emails.labels.date') }}:</strong> {{ $milestone->client_approved_at->translatedFormat('F j, Y \a\t g:i A') }}</li>
            </ul>

            @if($milestone->approval_note)
            <h4>
                {{ __('portal.emails.milestone_approved.clients') }}
                {{ $milestone->client_approved ? __('portal.emails.milestone_approved.feedback') : __('portal.emails.milestone_approved.rejection_reason') }}:
            </h4>
            <p style="background:white;padding:15px;border-left:4px solid {{ $milestone->client_approved ? '#10b981' : '#ef4444' }};font-style:italic;">"{{ $milestone->approval_note }}"</p>
            @endif

            <p style="margin-top:30px;">
                <a href="{{ route('projects.manager.show', $milestone->project) }}" 
                   style="display:inline-block;background:{{ $milestone->client_approved ? '#10b981' : '#ef4444' }};color:white;padding:12px 30px;text-decoration:none;border-radius:6px;font-weight:bold;">
                    {{ __('portal.emails.view_project') }}
                </a>
            </p>
        </div>
        <div class="footer">
            <p>{{ __('portal.emails.platform_notification') }}</p>
        </div>
    </div>
</body>
</html>
