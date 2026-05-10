<h2>Meeting booked</h2>
<p>A meeting was booked for <strong>{{ $meeting->title }}</strong>.</p>
<p><strong>Client:</strong> {{ $meeting->client->name }}</p>
<p><strong>Team member:</strong> {{ $meeting->teamMember->name }}</p>
<p><strong>Scheduled at:</strong> {{ $meeting->scheduled_at->format('M d, Y g:i A') }}</p>
<p>The team member can confirm the meeting from the portal.</p>
