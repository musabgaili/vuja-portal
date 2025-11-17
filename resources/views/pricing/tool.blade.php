@extends('layouts.internal-dashboard')
@section('title', 'Pricing Tool')

@section('breadcrumbs')
<li class="breadcrumb-item">Quote System</li>
<li class="breadcrumb-item active">Pricing Tool</li>
@endsection

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Inter', sans-serif; }
.pricing-header {
    background: linear-gradient(135deg, #10b981 0%, #1C575F 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.3);
}
.pricing-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.cart-card {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 2px solid #10b981;
}
.cart-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.5rem;
    margin-bottom: 0.4rem;
    transition: all 0.2s;
    font-size: 0.85rem;
}
.cart-item:hover {
    border-color: #10b981;
    box-shadow: 0 1px 4px rgba(16, 185, 129, 0.1);
}
.grand-total {
    background: linear-gradient(135deg, #10b981 0%, #1C575F 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-top: 1rem;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
}
</style>

<div class="pricing-header">
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-2">
            <i class="fas fa-calculator"></i> Internal Pricing Tool
        </h1>
        <p class="opacity-90">Build accurate quotes using standardized pricing rules</p>
        <a href="{{ route('pricing.admin') }}" class="btn btn-light btn-sm mt-3" style="background: white; color: #1C575F; border: 1px solid #1C575F;">
            <i class="fas fa-cogs"></i> Pricing Admin
        </a>
    </div>
</div>

<div class="row">
    <!-- Pricing Rules Table -->
    <div class="col-lg-8">
        <div class="pricing-card">
            <h3 class="text-xl font-semibold mb-4" style="color: #1e293b;">
                <i class="fas fa-list"></i> Available Pricing Rules
            </h3>
            <p class="text-sm text-muted mb-4">Click "Add to Quote" to add items. Edit quantities in the quote summary. Each click adds one item.</p>
            
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover">
                    <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                        <tr>
                            <th>Item / Level</th>
                            <th>Rate</th>
                            <th>Unit</th>
                            <th>Note</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="rulesTableBody">
                        @foreach($rules as $rule)
                        <tr style="cursor: pointer;" title="{{ $rule->note }}">
                            <td><strong>{{ $rule->item }}</strong><br><small class="text-muted">{{ $rule->level }}</small></td>
                            <td><strong class="text-success">${{ number_format($rule->rate, 2) }}</strong></td>
                            <td><span class="badge bg-secondary">{{ $rule->unit }}</span></td>
                            <td><small class="text-muted">{{ Str::limit($rule->note, 50) }}</small></td>
                            <td class="text-end">
                                <button onclick="addToCart({{ $rule->id }}, '{{ $rule->item }}', {{ $rule->rate }}, '{{ $rule->unit }}', '{{ $rule->level }}', '{{ addslashes($rule->note) }}')" 
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quote Summary -->
    <div class="col-lg-4">
        <div class="cart-card" style="position: sticky; top: 20px;">
            <h3 class="text-xl font-semibold mb-4" style="color: #1e293b;">
                <i class="fas fa-list-alt"></i> Quote Summary
            </h3>
            
            <div id="cartContainer" class="mb-3" style="max-height: 400px; overflow-y: auto;">
                <!-- Items will be rendered here -->
            </div>

            <div class="grand-total text-center">
                <p class="mb-0 opacity-90">Total</p>
                <h2 id="grandTotal" class="text-3xl font-bold mb-0">$0.00</h2>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];

function addToCart(id, item, rate, unit, level, note) {
    cart.push({
        id: id + '_' + Date.now(),
        item: item,
        rate: parseFloat(rate),
        unit: unit,
        level: level,
        note: note,
        quantity: 1
    });
    updateCart();
}

function updateQuantity(index, value) {
    cart[index].quantity = Math.max(1, parseInt(value) || 1);
    updateCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

function updateCart() {
    const container = document.getElementById('cartContainer');
    const totalEl = document.getElementById('grandTotal');
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-3"><i class="fas fa-inbox"></i><br><small>Add items to build your quote</small></p>';
        totalEl.textContent = '$0.00';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const lineTotal = item.rate * item.quantity;
        total += lineTotal;
        
        html += `
            <div class="cart-item">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div style="flex: 1;">
                        <strong style="color: #1e293b; font-size: 0.9rem;">${item.item}</strong>
                        <small class="badge bg-info ms-1" style="font-size: 0.65rem;">${item.level}</small>
                    </div>
                    <button onclick="removeFromCart(${index})" class="btn btn-sm btn-danger" style="padding: 0.2rem 0.4rem;">
                        <i class="fas fa-times" style="font-size: 0.7rem;"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <input type="number" 
                        value="${item.quantity}" 
                        min="1" 
                        onchange="updateQuantity(${index}, this.value)"
                        class="form-control form-control-sm" 
                        style="width: 60px; height: 28px; font-size: 0.8rem;">
                    <small class="text-muted" style="font-size: 0.75rem;">${item.unit} × $${item.rate.toFixed(2)}</small>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted" style="font-size: 0.7rem;">${item.note}</small>
                    <strong class="text-success" style="font-size: 0.9rem;">$${lineTotal.toFixed(2)}</strong>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    totalEl.textContent = '$' + total.toFixed(2);
}

// Initialize empty cart
updateCart();
</script>
@endpush


