@extends('layouts.internal-dashboard')
@section('title', __('portal.consultations.manager.show.title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('consultations.manager.index') }}">{{ __('portal.consultations.manager.index.breadcrumb') }}</a></li>
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
                <div class="mb-3"><strong>{{ __('portal.consultations.manager.show.category') }}:</strong> <span class="badge bg-info">{{ $consultation->categoryLabel() }}</span></div>
                <div class="mb-3"><strong>{{ __('portal.consultations.manager.show.description') }}:</strong><p>{{ $consultation->description }}</p></div>
                @if($consultation->preferred_date)
                <div class="mb-3"><strong>{{ __('portal.consultations.manager.show.preferred_date') }}:</strong> {{ $consultation->preferred_date }}</div>
                @endif
                @if($consultation->preferred_time)
                <div class="mb-3"><strong>{{ __('portal.consultations.manager.show.preferred_time') }}:</strong> {{ $consultation->preferred_time }}</div>
                @endif
                @if($consultation->meeting_scheduled_at)
                <div class="mb-3"><strong>{{ __('portal.consultations.manager.show.meeting_scheduled') }}:</strong> {{ $consultation->meeting_scheduled_at->translatedFormat('M d, Y H:i') }}</div>
                @endif
                @if($consultation->meeting_link)
                <div class="mb-3"><strong>{{ __('portal.consultations.manager.show.meeting_link') }}:</strong> <a href="{{ $consultation->meeting_link }}" target="_blank">{{ $consultation->meeting_link }}</a></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3>{{ __('portal.consultations.manager.show.client_information') }}</h3></div>
            <div class="card-content">
                <p><strong>{{ __('portal.consultations.manager.show.name') }}:</strong> {{ $consultation->user->name }}</p>
                <p><strong>{{ __('portal.consultations.manager.show.email') }}:</strong> <a href="mailto:{{ $consultation->user->email }}">{{ $consultation->user->email }}</a></p>
                @if($consultation->user->phone)<p><strong>{{ __('portal.consultations.manager.show.phone') }}:</strong> <a href="tel:{{ $consultation->user->phone }}">{{ $consultation->user->phone }}</a></p>@endif
                <p><strong>{{ __('portal.consultations.manager.show.submitted') }}:</strong> {{ $consultation->created_at->translatedFormat('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h3>{{ __('portal.consultations.manager.show.actions') }}</h3></div>
            <div class="card-content">
                @if(!$consultation->assignedTo)
                <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> {{ __('portal.consultations.manager.show.assign_employee') }}</button>
                @else
                <p><strong>{{ __('portal.consultations.manager.show.assigned_to') }}:</strong> {{ $consultation->assignedTo->name }}</p>
                @endif
                @if($consultation->isAssigned() || $consultation->isMeetingScheduled())
                <button class="btn btn-primary btn-block mb-2" onclick="showMeetingModal()"><i class="fas fa-calendar"></i> {{ __('portal.consultations.manager.show.send_meeting_invite') }}</button>
                @endif
                @if($consultation->isMeetingSent())
                <button class="btn btn-success btn-block mb-2" onclick="markComplete()"><i class="fas fa-check"></i> {{ __('portal.consultations.manager.show.mark_complete') }}</button>
                @endif
                @if($consultation->status === 'completed')
                    @php $convertedProject = $consultation->convertedProject(); @endphp
                    @if($convertedProject)
                    <a href="{{ route('projects.manager.show', $convertedProject) }}" class="btn btn-outline-primary btn-block mb-2"><i class="fas fa-diagram-project"></i> {{ __('portal.services.view_project') }}</a>
                    @else
                    <form method="POST" action="{{ route('consultations.convert-to-project', $consultation) }}">@csrf
                        <button class="btn btn-primary btn-block mb-2"><i class="fas fa-diagram-project"></i> {{ __('portal.services.convert_to_project') }}</button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@include('partials.service-work-panel', ['model' => $consultation, 'type' => 'consultation'])

<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.consultations.manager.show.assign_employee') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('consultations.assign', $consultation) }}">@csrf<div class="modal-body"><select name="assigned_to" class="form-control" required><option value="">{{ __('portal.consultations.manager.show.select_employee') }}</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="modal-footer"><button type="submit" class="btn btn-primary">{{ __('portal.consultations.manager.show.assign') }}</button></div></form></div></div></div>
<div class="modal fade" id="meetingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.consultations.manager.show.schedule_meeting') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('consultations.send-invite', $consultation) }}">
                @csrf
                <div class="modal-body">
                    @if($consultation->assignedTo)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ __('portal.consultations.manager.show.assigned_to') }}:</strong> {{ $consultation->assignedTo->name }}
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.consultations.manager.show.available_time_slots') }}</label>
                        <div id="availableSlots" class="row">
                            <!-- Available slots will be loaded here -->
                        </div>
                        <div id="loadingSlots" class="text-center py-3">
                            <i class="fas fa-spinner fa-spin"></i> {{ __('portal.consultations.manager.show.loading_available_slots') }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label fw-bold">{{ __('portal.consultations.manager.show.meeting_link_optional') }}</label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="{{ __('portal.consultations.manager.show.meeting_link_placeholder') }}">
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('portal.consultations.manager.show.assign_employee_first') }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.consultations.manager.show.cancel') }}</button>
                    @if($consultation->assignedTo)
                    <button type="submit" class="btn btn-primary" id="scheduleBtn" disabled>
                        <i class="fas fa-calendar-check"></i> {{ __('portal.consultations.manager.show.schedule_meeting') }}
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
                            <strong>${@js(__('portal.consultations.manager.show.no_available_slots'))}</strong><br>
                            ${@js(__('portal.consultations.manager.show.no_available_slots_body'))}
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
                        <strong>${@js(__('portal.consultations.manager.show.error_loading_slots'))}</strong><br>
                        ${@js(__('portal.consultations.manager.show.error_loading_slots_body'))}
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
        const formattedDate = new Date(date).toLocaleDateString(@js(app()->getLocale() === 'ar' ? 'ar' : 'en-US'), {
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
                        <div class="slot-duration">${slot.duration} ${@js(__('portal.consultations.manager.show.min'))}</div>
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
    if(confirm(@js(__('portal.consultations.manager.show.confirm_mark_complete')))){
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
 
