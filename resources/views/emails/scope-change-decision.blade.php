<h2>Scope change {{ $scopeChange->status }}</h2>
<p>Your scope change request for <strong>{{ $scopeChange->project->title }}</strong> was <strong>{{ $scopeChange->status }}</strong>.</p>
<p><strong>Request:</strong> {{ $scopeChange->title }}</p>
@if($scopeChange->review_notes)
<p><strong>Review notes:</strong> {{ $scopeChange->review_notes }}</p>
@endif
<p>You can view the updated request status in your project portal.</p>
