@extends('layouts.internal-dashboard')
@section('title', 'Consultation Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('consultations.manager.index') }}">Consultations</a></li>
<li class="breadcrumb-item active">#{{ $consultation->id }} - {{ Str::limit($consultation->title, 30) }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $consultation->title }}</h3>
                <span class="status-badge {{ $consultation->getStatusBadgeColor() }}">{{ $consultation->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="mb-3"><strong>Category:</strong> <span class="badge bg-info">{{ $consultation->category }}</span></div>
                <div class="mb-3"><strong>Description:</strong><p>{{ $consultation->description }}</p></div>
                @if($consultation->preferred_date)
                <div class="mb-3"><strong>Preferred Date:</strong> {{ $consultation->preferred_date }}</div>
                @endif
                @if($consultation->preferred_time)
                <div class="mb-3"><strong>Preferred Time:</strong> {{ $consultation->preferred_time }}</div>
                @endif
                @if($consultation->meeting_scheduled_at)
                <div class="mb-3"><strong>Meeting Scheduled:</strong> {{ $consultation->meeting_scheduled_at->format('M d, Y H:i') }}</div>
                @endif
                @if($consultation->meeting_link)
                <div class="mb-3"><strong>Meeting Link:</strong> <a href="{{ $consultation->meeting_link }}" target="_blank">{{ $consultation->meeting_link }}</a></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3>Client Information</h3></div>
            <div class="card-content">
                <p><strong>Name:</strong> {{ $consultation->user->name }}</p>
                <p><strong>Email:</strong> <a href="mailto:{{ $consultation->user->email }}">{{ $consultation->user->email }}</a></p>
                @if($consultation->user->phone)<p><strong>Phone:</strong> <a href="tel:{{ $consultation->user->phone }}">{{ $consultation->user->phone }}</a></p>@endif
                <p><strong>Submitted:</strong> {{ $consultation->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h3>Actions</h3></div>
            <div class="card-content">
                @if(!$consultation->assignedTo)
                <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> Assign Employee</button>
                @else
                <p><strong>Assigned to:</strong> {{ $consultation->assignedTo->name }}</p>
                @endif
                @if($consultation->isAssigned() || $consultation->isMeetingScheduled())
                <button class="btn btn-primary btn-block mb-2" onclick="showMeetingModal()"><i class="fas fa-calendar"></i> Send Meeting Invite</button>
                @endif
                @if($consultation->isMeetingSent())
                <button class="btn btn-success btn-block mb-2" onclick="markComplete()"><i class="fas fa-check"></i> Mark Complete</button>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Assign Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('consultations.assign', $consultation) }}">@csrf<div class="modal-body"><select name="assigned_to" class="form-control" required><option value="">Select employee...</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Assign</button></div></form></div></div></div>
<div class="modal fade" id="meetingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Schedule Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('consultations.send-invite', $consultation) }}">
                @csrf
                <div class="modal-body">
                    @if($consultation->assignedTo)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Assigned to:</strong> {{ $consultation->assignedTo->name }}
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Available Time Slots</label>
                        <div id="availableSlots" class="row">
                            <!-- Available slots will be loaded here -->
                        </div>
                        <div id="loadingSlots" class="text-center py-3">
                            <i class="fas fa-spinner fa-spin"></i> Loading available slots...
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label fw-bold">Meeting Link (Optional)</label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/...">
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Please assign an employee first before scheduling a meeting.
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if($consultation->assignedTo)
                    <button type="submit" class="btn btn-primary" id="scheduleBtn" disabled>
                        <i class="fas fa-calendar-check"></i> Schedule Meeting
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
let selectedSlotId = null;

function showAssignModal(){
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function showMeetingModal(){
    const modal = new bootstrap.Modal(document.getElementById('meetingModal'));
    modal.show();
    
    // Load available slots when modal opens
    if ({{ $consultation->assignedTo ? $consultation->assignedTo->id : 'null' }}) {
        loadAvailableSlots({{ $consultation->assignedTo->id ?? 'null' }});
    }
}

function loadAvailableSlots(employeeId) {
    const slotsContainer = document.getElementById('availableSlots');
    const loadingDiv = document.getElementById('loadingSlots');
    
    // Show loading
    loadingDiv.style.display = 'block';
    slotsContainer.innerHTML = '';
    
    // Fetch available slots
    fetch(`/internal/time-slots/available/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            loadingDiv.style.display = 'none';
            
            if (data.slots && data.slots.length > 0) {
                renderTimeSlots(data.slots);
            } else {
                slotsContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-calendar-times"></i>
                            <strong>No Available Slots</strong><br>
                            This employee has no available time slots. Please ask them to create some availability.
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            loadingDiv.style.display = 'none';
            slotsContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error Loading Slots</strong><br>
                        Unable to load available time slots. Please try again.
                    </div>
                </div>
            `;
        });
}

function renderTimeSlots(slots) {
    const slotsContainer = document.getElementById('availableSlots');
    let html = '';
    
    // Group slots by date
    const groupedSlots = {};
    slots.forEach(slot => {
        const date = slot.date;
        if (!groupedSlots[date]) {
            groupedSlots[date] = [];
        }
        groupedSlots[date].push(slot);
    });
    
    // Render grouped slots
    Object.keys(groupedSlots).forEach(date => {
        const dateSlots = groupedSlots[date];
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        html += `
            <div class="col-12 mb-3">
                <h6 class="text-primary fw-bold">${formattedDate}</h6>
                <div class="row">
        `;
        
        dateSlots.forEach(slot => {
            html += `
                <div class="col-md-4 col-lg-3 mb-2">
                    <div class="slot-card" onclick="selectSlot(${slot.id}, '${slot.start_time}', '${slot.end_time}', '${slot.date}')">
                        <div class="slot-time">${slot.start_time} - ${slot.end_time}</div>
                        <div class="slot-duration">${slot.duration} min</div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    slotsContainer.innerHTML = html;
}

function selectSlot(slotId, startTime, endTime, date) {
    // Remove previous selection
    document.querySelectorAll('.slot-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selection to clicked card
    event.target.closest('.slot-card').classList.add('selected');
    
    // Store selected slot info
    selectedSlotId = slotId;
    
    // Add hidden input to form
    let hiddenInput = document.querySelector('input[name="time_slot_id"]');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'time_slot_id';
        document.querySelector('#meetingModal form').appendChild(hiddenInput);
    }
    hiddenInput.value = slotId;
    
    // Enable schedule button
    document.getElementById('scheduleBtn').disabled = false;
}

function markComplete(){
    if(confirm('Mark consultation as complete?')){
        fetch('{{ route('consultations.complete', $consultation) }}',{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            }
        }).then(()=>location.reload());
    }
}
</script>

<style>
.slot-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}
.slot-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}
.slot-card.selected {
    border-color: #10b981;
    background: #f0fdf4;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}
.slot-time {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}
.slot-duration {
    font-size: 0.875rem;
    color: #64748b;
}
</style>
@endpush
 
