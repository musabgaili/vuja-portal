@extends('layouts.internal-dashboard')
@section('title', 'Projects Kanban')
@section('breadcrumbs')
<li class="breadcrumb-item">Projects</li>
<li class="breadcrumb-item active">Kanban View</li>
@endsection

@section('content')
<style>
.projects-header{background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);color:white;padding:2rem;border-radius:12px;margin-bottom:1.5rem;box-shadow:0 4px 16px rgba(99,102,241,0.3);}
.stat-cards-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;}
.stat-card-proj{background:white;border-radius:12px;padding:1.5rem;box-shadow:0 4px 16px rgba(0,0,0,0.08);border-left:4px solid;transition:all 0.3s;}
.stat-card-proj:hover{transform:translateY(-4px);box-shadow:0 8px 24px rgba(0,0,0,0.12);}
.stat-number-proj{font-size:2.5rem;font-weight:700;}
.stat-label-proj{color:#64748b;font-size:0.875rem;text-transform:uppercase;margin-top:0.5rem;}

/* Kanban Container with Horizontal Scroll */
.kanban-scroll-wrapper{
    background:white;
    border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,0.08);
    padding:1.5rem;
    overflow-x:auto;
    overflow-y:hidden;
}
.kanban-container{
    display:flex;
    gap:1.5rem;
    min-width:min-content;
    padding-bottom:0.5rem;
}
.kanban-column{
    background:#f8fafc;
    border-radius:12px;
    padding:1rem;
    min-width:340px;
    max-width:340px;
    flex-shrink:0;
}
.kanban-header{
    font-weight:700;
    font-size:1rem;
    margin-bottom:1rem;
    padding:0.75rem 1rem;
    border-radius:8px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:white;
}
.kanban-cards-area{
    max-height:calc(100vh - 450px);
    min-height:400px;
    overflow-y:auto;
    padding-right:0.5rem;
}
.kanban-card{
    background:white;
    border-radius:10px;
    padding:1.25rem;
    margin-bottom:1rem;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    cursor:grab;
    transition:all 0.2s ease;
    border-left:4px solid;
}
.kanban-card:active{cursor:grabbing;}
.kanban-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 20px rgba(0,0,0,0.12);
}
.kanban-card.dragging{opacity:0.4;transform:rotate(2deg);}
.kanban-column.drag-over{background:#e0e7ff;transform:scale(1.02);transition:all 0.2s;}
.project-title{
    font-weight:700;
    color:#1e293b;
    margin-bottom:0.75rem;
    font-size:1rem;
    line-height:1.3;
}
.info-row{
    display:flex;
    align-items:center;
    gap:0.5rem;
    margin-bottom:0.5rem;
    font-size:0.85rem;
}
.client-badge{
    background:#f1f5f9;
    padding:0.35rem 0.75rem;
    border-radius:6px;
    font-size:0.8rem;
    color:#475569;
    font-weight:600;
}
.deadline-badge{
    padding:0.35rem 0.75rem;
    border-radius:6px;
    font-size:0.8rem;
    font-weight:700;
}
.deadline-badge.overdue{background:#fee2e2;color:#991b1b;}
.deadline-badge.soon{background:#fef3c7;color:#92400e;}
.deadline-badge.safe{background:#d1fae5;color:#065f46;}
.progress-wrapper{margin:0.75rem 0;}
.progress-bar-custom{
    height:8px;
    background:#e2e8f0;
    border-radius:10px;
    overflow:hidden;
}
.progress-fill{
    height:100%;
    background:linear-gradient(90deg,#10b981,#059669);
    border-radius:10px;
    transition:width 0.3s;
}
.empty-column{
    text-align:center;
    padding:3rem 1rem;
    color:#94a3b8;
    font-size:0.9rem;
}
</style>

<div class="projects-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 style="margin:0;font-size:1.75rem;font-weight:700;"><i class="fas fa-th"></i> Projects Kanban</h1>
            <p style="margin:0.5rem 0 0 0;opacity:0.95;">Drag & drop cards to update project status instantly</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('projects.manager.index') }}" class="btn btn-outline-light">
                <i class="fas fa-table"></i> Table
            </a>
            <a href="{{ route('projects.kanban') }}" class="btn btn-light">
                <i class="fas fa-th"></i> Kanban
            </a>
            @if(auth()->user()->isManager())
            <a href="{{ route('projects.create') }}" class="btn btn-warning">
                <i class="fas fa-plus"></i> New Project
            </a>
            @endif
        </div>
    </div>
</div>

<div class="kanban-scroll-wrapper">
    <div class="kanban-container">
        @foreach(['planning', 'quoted', 'awarded', 'in_progress', 'paused', 'completed'] as $status)
        @php
            $colors = [
                'planning' => ['bg' => '#3b82f6', 'name' => 'Planning'],
                'quoted' => ['bg' => '#8b5cf6', 'name' => 'Quoted'],
                'awarded' => ['bg' => '#10b981', 'name' => 'Awarded'],
                'in_progress' => ['bg' => '#f59e0b', 'name' => 'In Progress'],
                'paused' => ['bg' => '#ef4444', 'name' => 'Paused'],
                'completed' => ['bg' => '#059669', 'name' => 'Completed']
            ];
            $statusProjects = $projects->where('status', $status);
        @endphp
        <div class="kanban-column" data-status="{{ $status }}" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
            <div class="kanban-header" style="background:{{ $colors[$status]['bg'] }};">
                <span>{{ $colors[$status]['name'] }}</span>
                <span class="badge bg-white text-dark">{{ $statusProjects->count() }}</span>
            </div>
            
            <div class="kanban-cards-area">
                @forelse($statusProjects as $project)
                <div class="kanban-card" 
                     draggable="true" 
                     ondragstart="drag(event)" 
                     ondragend="dragEnd(event)"
                     data-project-id="{{ $project->id }}"
                     style="border-color:{{ $colors[$status]['bg'] }};">
                    
                    <div class="project-title">{{ $project->title }}</div>
                    
                    <div class="info-row">
                        @if($project->client)
                        <span class="client-badge">
                            <i class="fas fa-user"></i> {{ $project->client->name }}
                        </span>
                        @else
                        <span class="client-badge" style="background:#fee2e2;color:#991b1b;">
                            <i class="fas fa-user-slash"></i> No Client
                        </span>
                        @endif
                    </div>
                    
                    @if($project->end_date)
                    @php
                        $now = now();
                        $endDate = $project->end_date;
                        $daysLeft = $now->diffInDays($endDate, false);
                        $class = $daysLeft < 0 ? 'overdue' : ($daysLeft <= 3 ? 'soon' : 'safe');
                        $daysText = $daysLeft < 0 ? abs($daysLeft).' days overdue' : $daysLeft.' days';
                    @endphp
                    <div class="info-row">
                        <span class="deadline-badge {{ $class }}">
                            <i class="fas fa-calendar-alt"></i> {{ $endDate->format('M d, Y') }} • {{ $daysText }}
                        </span>
                    </div>
                    @endif
                    
                    <div class="progress-wrapper">
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width:{{ $project->completion_percentage }}%;"></div>
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">
                            <i class="fas fa-tasks"></i> {{ $project->completion_percentage }}% Complete
                        </small>
                    </div>
                    
                    <div class="mt-2">
                        <a href="{{ route('projects.manager.show', $project) }}" 
                           class="btn btn-sm btn-outline-primary w-100" 
                           onclick="event.stopPropagation();"
                           style="border-color:{{ $colors[$status]['bg'] }};color:{{ $colors[$status]['bg'] }};">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
                @empty
                <div class="empty-column">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>No projects in {{ $colors[$status]['name'] }}</p>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
let draggedElement = null;

function drag(event) {
    // Only allow dragging if the element itself is the card (to prevent issues with clicking buttons)
    if (event.target.classList.contains('kanban-card')) {
        draggedElement = event.target;
        event.dataTransfer.setData('text/plain', event.target.dataset.projectId);
        event.target.classList.add('dragging');
    }
}

function dragEnd(event) {
    if (draggedElement) {
        draggedElement.classList.remove('dragging');
        draggedElement = null;
    }
}

function allowDrop(event) {
    event.preventDefault();
    // Only apply drag-over style to kanban-column elements
    if (event.currentTarget.classList.contains('kanban-column')) {
        event.currentTarget.classList.add('drag-over');
    }
}

function dragLeave(event) {
    if (event.currentTarget.classList.contains('kanban-column')) {
        event.currentTarget.classList.remove('drag-over');
    }
}

function drop(event) {
    event.preventDefault();
    
    const column = event.currentTarget;
    column.classList.remove('drag-over');
    
    if (draggedElement) {
        const cardsArea = column.querySelector('.kanban-cards-area');
        const newStatus = column.dataset.status;
        const projectId = draggedElement.dataset.projectId;
        
        // 1. Move card to new column
        cardsArea.appendChild(draggedElement);
        
        // 2. Update border and button colors
        const colors = {
            'planning': '#3b82f6',
            'quoted': '#8b5cf6',
            'awarded': '#10b981',
            'in_progress': '#f59e0b',
            'paused': '#ef4444',
            'completed': '#059669'
        };
        draggedElement.style.borderColor = colors[newStatus];
        
        const btn = draggedElement.querySelector('.btn-outline-primary');
        if (btn) {
            btn.style.borderColor = colors[newStatus];
            btn.style.color = colors[newStatus];
        }
        
        // 3. Update backend and UI counts
        updateProjectStatus(projectId, newStatus);
    }
}

function updateProjectStatus(projectId, newStatus) {
    fetch(`/internal/projects/${projectId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateColumnCounts();
            updateEmptyStates();
            showToast('✓ Project status updated successfully!');
        } else {
            showToast('✗ Error updating project status. Reloading.', 'error');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('✗ Network error occurred. Reloading.', 'error');
        location.reload();
    });
}

function updateColumnCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        // Count cards within the cards area specifically
        const count = column.querySelector('.kanban-cards-area').querySelectorAll('.kanban-card').length;
        const badge = column.querySelector('.badge');
        if (badge) badge.textContent = count;
    });
}

function updateEmptyStates() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const cardsArea = column.querySelector('.kanban-cards-area');
        const cards = cardsArea.querySelectorAll('.kanban-card');
        let emptyState = cardsArea.querySelector('.empty-column'); // Search within cardsArea
        
        if (cards.length === 0) {
            if (!emptyState) { // Add empty state only if it doesn't exist
                const statusName = column.dataset.status.replace(/_/g, ' ');
                const statusNameDisplay = statusName.charAt(0).toUpperCase() + statusName.slice(1);
                
                cardsArea.innerHTML = `
                    <div class="empty-column">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No projects in ${statusNameDisplay}</p>
                    </div>
                `;
            }
        } else if (emptyState) {
            // Remove the empty state if cards are now present
            emptyState.remove();
        }
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#ef4444,#dc2626)'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        z-index: 9999;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Run on page load to ensure initial state is correct
document.addEventListener('DOMContentLoaded', () => {
    updateColumnCounts();
    updateEmptyStates();
});
</script>
@endpush
