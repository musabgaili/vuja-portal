<h2>New scope change request</h2>
<p>A client requested a scope change for <strong>{{ $scopeChange->project->title }}</strong>.</p>
<p><strong>Request:</strong> {{ $scopeChange->title }}</p>
<p>{{ $scopeChange->description }}</p>
@if($scopeChange->justification)
<p><strong>Justification:</strong> {{ $scopeChange->justification }}</p>
@endif
<p>Please review the request from the internal scope changes queue.</p>
