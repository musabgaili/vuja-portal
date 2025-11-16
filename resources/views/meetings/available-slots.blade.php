@extends('layouts.dashboard')
@section('title', 'Book a Meeting')
@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h3>Book a Meeting</h3>
    </div>
    <div class="card-content">
        @if(isset($hasAssignedConsultants) && !$hasAssignedConsultants)
            <div class="alert alert-warning text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>No Assigned Consultants</h4>
                <p>You can only book meetings for service requests that have been assigned to a consultant.</p>
                <p class="mb-0">Please wait for a consultant to be assigned to your service request, or contact support for assistance.</p>
            </div>
        @else
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Select Service Request *</label>
                    <select name="service_request_id" class="form-control" required onchange="updateServiceType(this)">
                        <option value="">-- Select a Service Request --</option>
                        @foreach($serviceRequests ?? [] as $request)
                        <option value="{{ $request['id'] }}" 
                                data-type="{{ $request['type'] }}"
                                {{ $selectedServiceRequest == $request['id'] ? 'selected' : '' }}>
                            {{ ucfirst($request['type']) }}: {{ $request['title'] }}
                        </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="service_type" id="service_type" value="{{ $selectedType ?? '' }}">
                    <small class="form-text text-muted">
                        You can only book meetings for service requests that have been assigned to a consultant.
                    </small>
                </div>
            </form>

            @if($slots && $slots->count() > 0)
        <div class="slots-grid">
            @foreach($slots->groupBy(fn($s) => $s->date->format('Y-m-d')) as $date => $daySlots)
            <div class="day-section mb-4">
                <h5 class="mb-3">{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</h5>
                <div class="row">
                    @foreach($daySlots as $slot)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="slot-card {{ $slot->isAvailable() ? 'available' : 'unavailable' }}">
                            <div class="slot-time">{{ $slot->getFormattedTimeRange() }}</div>
                            <div class="slot-team">
                                <i class="fas fa-user"></i> {{ $slot->user->name }}
                            </div>
                            @if($slot->isAvailable() && $selectedServiceRequest && $selectedType)
                            <a href="{{ route('meetings.create', ['timeSlot' => $slot->id, 'service_request_id' => $selectedServiceRequest, 'service_type' => $selectedType]) }}" class="btn btn-sm btn-success btn-block mt-2">
                                <i class="fas fa-calendar-check"></i> Book
                            </a>
                            @elseif(!$selectedServiceRequest || !$selectedType)
                            <button class="btn btn-sm btn-secondary btn-block mt-2" disabled>
                                <i class="fas fa-info-circle"></i> Select Service Request
                            </button>
                            @else
                            <button class="btn btn-sm btn-danger btn-block mt-2" disabled>
                                <i class="fas fa-times"></i> Booked
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
            {{ $slots->links('pagination::bootstrap-5') }}
            @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h4>No Available Slots</h4>
                <p>Please select a service request to see available time slots for the assigned consultant.</p>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
@push('scripts')
<script>
function updateServiceType(select) {
    const selectedOption = select.options[select.selectedIndex];
    const serviceType = selectedOption.getAttribute('data-type');
    document.getElementById('service_type').value = serviceType || '';
    
    // Submit form to filter slots
    if (select.value) {
        select.form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
.slot-card{background:var(--card-bg);padding:var(--space-md);border-radius:var(--radius-md);border:2px solid var(--gray-200);transition:all 0.3s;}
.slot-card.available{border-color:var(--success-color);}
.slot-card.available:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);}
.slot-card.unavailable{border-color:var(--error-color);opacity:0.6;}
.slot-time{font-size:var(--font-size-lg);font-weight:600;color:var(--primary-color);margin-bottom:var(--space-sm);}
.slot-team{font-size:var(--font-size-sm);color:var(--gray-600);margin-bottom:var(--space-sm);}
.day-section{padding:var(--space-lg);background:var(--bg-secondary);border-radius:var(--radius-md);}
</style>
@endpush

