@extends('layouts.internal-dashboard')
@section('title', 'Create Time Slots')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('time-slots.my-slots') }}">Time Slots</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<style>
.create-slots-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}
.card-modern-form {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}
.card-modern-form .card-content {
    padding: 2rem;
}
</style>

<div class="create-slots-header">
    <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700;"><i class="fas fa-calendar-plus"></i> Create Availability Schedule</h1>
    <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">Set up your time slots for client bookings</p>
</div>

<div class="card-modern-form">
    <div class="card-content">
        <form method="POST" action="{{ route('time-slots.store') }}" id="slotForm">
            @csrf
            
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i>
                <strong>How it works:</strong> Select the days and times you're available. Each selected cell = 1-hour slot.
            </div>

            <!-- Week Selection -->
            <div class="mb-4">
                <h5>Select Days (Saudi Work Week: Sunday-Thursday)</h5>
                <div class="day-selector">
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="sunday" checked>
                        <span>Sunday</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="monday" checked>
                        <span>Monday</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="tuesday" checked>
                        <span>Tuesday</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="wednesday" checked>
                        <span>Wednesday</span>
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="thursday" checked>
                        <span>Thursday</span>
                    </label>
                    <label class="day-checkbox weekend">
                        <input type="checkbox" name="days[]" value="friday">
                        <span>Friday <small>(Weekend)</small></span>
                    </label>
                    <label class="day-checkbox weekend">
                        <input type="checkbox" name="days[]" value="saturday">
                        <span>Saturday <small>(Weekend)</small></span>
                    </label>
                </div>
            </div>

            <!-- Time Slot Grid -->
            <div class="mb-4">
                <h5>Select Available Time Slots (Click to toggle)</h5>
                <p class="text-muted">Green = Available, Gray = Not Available</p>
                
                <div class="time-grid">
                    @php
                    $hours = [
                        '09:00' => '9:00 AM',
                        '10:00' => '10:00 AM',
                        '11:00' => '11:00 AM',
                        '12:00' => '12:00 PM',
                        '13:00' => '1:00 PM',
                        '14:00' => '2:00 PM',
                        '15:00' => '3:00 PM',
                        '16:00' => '4:00 PM',
                        '17:00' => '5:00 PM',
                        '18:00' => '6:00 PM',
                        '19:00' => '7:00 PM',
                        '20:00' => '8:00 PM',
                        '21:00' => '9:00 PM',
                        '22:00' => '10:00 PM',
                    ];
                    @endphp

                    @foreach($hours as $time => $label)
                    <div class="time-slot-item" onclick="toggleSlot(this)" data-time="{{ $time }}">
                        <input type="checkbox" name="time_slots[]" value="{{ $time }}" style="display:none;">
                        <div class="slot-label">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-success" onclick="selectWorkingHours()">
                        <i class="fas fa-briefcase"></i> Working Hours (9-5)
                    </button>
                    <button type="button" class="btn btn-sm btn-info" onclick="selectMorning()">
                        <i class="fas fa-sunrise"></i> Morning (9-12)
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="selectAfternoon()">
                        <i class="fas fa-sun"></i> Afternoon (1-5)
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="selectEvening()">
                        <i class="fas fa-moon"></i> Evening (6-10)
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectAll()">
                        <i class="fas fa-check-double"></i> All Hours
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="clearAll()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Duration for slots -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" class="form-control" min="{{ today()->format('Y-m-d') }}" value="{{ today()->addDay()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Number of Weeks</label>
                        <select name="weeks" class="form-control">
                            <option value="1">1 Week</option>
                            <option value="2" selected>2 Weeks</option>
                            <option value="3">3 Weeks</option>
                            <option value="4">4 Weeks</option>
                        </select>
                        <small class="text-muted">Create slots for next X weeks</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Slot Duration</label>
                        <select name="slot_duration" class="form-control">
                            <option value="60" selected>60 minutes</option>
                            <option value="30">30 minutes</option>
                            <option value="90">90 minutes</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label>Notes (Optional)</label>
                <textarea name="notes" rows="2" class="form-control" placeholder="e.g., General consultation slots">{{ old('notes') }}</textarea>
            </div>

            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>Preview:</strong> <span id="slotCount">0</span> time slots will be created based on your selection.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Time Slots
                </button>
                <a href="{{ route('time-slots.my-slots') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.day-selector{display:flex;gap:var(--space-sm);flex-wrap:wrap;margin-bottom:var(--space-lg);}
.day-checkbox{display:flex;align-items:center;padding:var(--space-md);background:var(--bg-secondary);border:2px solid var(--gray-300);border-radius:var(--radius-md);cursor:pointer;transition:all 0.2s;}
.day-checkbox input{margin-right:var(--space-sm);}
.day-checkbox:has(input:checked){background:var(--primary-color);color:white;border-color:var(--primary-color);}
.day-checkbox.weekend{background:var(--gray-100);opacity:0.7;}
.day-checkbox.weekend small{color:var(--error-color);font-weight:600;}
.time-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:var(--space-sm);}
.time-slot-item{padding:var(--space-md);background:var(--gray-200);border:2px solid var(--gray-300);border-radius:var(--radius-md);text-align:center;cursor:pointer;transition:all 0.2s;user-select:none;}
.time-slot-item:hover{transform:translateY(-2px);box-shadow:var(--shadow-sm);}
.time-slot-item.selected{background:var(--success-color);color:white;border-color:var(--success-color);}
.time-slot-item.selected input{display:block;}
.slot-label{font-weight:600;font-size:var(--font-size-sm);}
</style>
@endpush

@push('scripts')
<script>
function toggleSlot(el){
    el.classList.toggle('selected');
    const checkbox = el.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    updateCount();
}

function selectAll(){
    document.querySelectorAll('.time-slot-item').forEach(el => {
        el.classList.add('selected');
        el.querySelector('input').checked = true;
    });
    updateCount();
}

function clearAll(){
    document.querySelectorAll('.time-slot-item').forEach(el => {
        el.classList.remove('selected');
        el.querySelector('input').checked = false;
    });
    updateCount();
}

function selectWorkingHours(){
    clearAll();
    const workingTimes = ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
    document.querySelectorAll('.time-slot-item').forEach(el => {
        if(workingTimes.includes(el.dataset.time)){
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }
    });
    updateCount();
}

function selectMorning(){
    clearAll();
    const morningTimes = ['09:00','10:00','11:00','12:00'];
    document.querySelectorAll('.time-slot-item').forEach(el => {
        if(morningTimes.includes(el.dataset.time)){
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }
    });
    updateCount();
}

function selectAfternoon(){
    clearAll();
    const afternoonTimes = ['13:00','14:00','15:00','16:00','17:00'];
    document.querySelectorAll('.time-slot-item').forEach(el => {
        if(afternoonTimes.includes(el.dataset.time)){
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }
    });
    updateCount();
}

function selectEvening(){
    clearAll();
    const eveningTimes = ['18:00','19:00','20:00','21:00','22:00'];
    document.querySelectorAll('.time-slot-item').forEach(el => {
        if(eveningTimes.includes(el.dataset.time)){
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }
    });
    updateCount();
}

function updateCount(){
    const selectedSlots = document.querySelectorAll('.time-slot-item.selected').length;
    const selectedDays = document.querySelectorAll('.day-checkbox input:checked').length;
    const weeks = parseInt(document.querySelector('select[name="weeks"]').value);
    const total = selectedSlots * selectedDays * weeks;
    document.getElementById('slotCount').textContent = total;
}

// Update count on page load and when days/weeks change
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.day-checkbox input, select[name="weeks"]').forEach(el => {
        el.addEventListener('change', updateCount);
    });
    updateCount();
});
</script>
@endpush
