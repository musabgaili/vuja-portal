<h2>Meeting confirmed</h2>
<p>Your meeting for <strong>{{ $meeting->title }}</strong> has been confirmed.</p>
<p><strong>Scheduled at:</strong> {{ $meeting->scheduled_at->translatedFormat('M d, Y g:i A') }}</p>
@if($meeting->meeting_link)
<p><strong>Meeting link:</strong> <a href="{{ $meeting->meeting_link }}">{{ $meeting->meeting_link }}</a></p>
@endif
<p>Please check your portal for the latest meeting details.</p>
