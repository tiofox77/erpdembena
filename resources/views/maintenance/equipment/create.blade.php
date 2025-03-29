@extends('layouts.maintenance')

@section('page-title', 'Add Equipment')

@section('content')
<div class="equipment-form">
    <h2>Add New Equipment</h2>

    <div class="section">
        <form class="form-container">
            <div class="form-group">
                <label for="equipment_name">Equipment Name</label>
                <input type="text" id="equipment_name" name="equipment_name" class="form-control" placeholder="Enter equipment name" required>
            </div>

            <div class="form-group">
                <label for="equipment_type">Equipment Type</label>
                <select id="equipment_type" name="equipment_type" class="form-control" required>
                    <option value="">Select type</option>
                    <option value="mechanical">Mechanical</option>
                    <option value="electrical">Electrical</option>
                    <option value="hydraulic">Hydraulic</option>
                    <option value="pneumatic">Pneumatic</option>
                </select>
            </div>

            <div class="form-group">
                <label for="model_number">Model Number</label>
                <input type="text" id="model_number" name="model_number" class="form-control" placeholder="Enter model number">
            </div>

            <div class="form-group">
                <label for="serial_number">Serial Number</label>
                <input type="text" id="serial_number" name="serial_number" class="form-control" placeholder="Enter serial number">
            </div>

            <div class="form-group">
                <label for="manufacturer">Manufacturer</label>
                <input type="text" id="manufacturer" name="manufacturer" class="form-control" placeholder="Enter manufacturer">
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" class="form-control" placeholder="Enter location">
            </div>

            <div class="form-group">
                <label for="purchase_date">Purchase Date</label>
                <input type="date" id="purchase_date" name="purchase_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="warranty_expiry">Warranty Expiry</label>
                <input type="date" id="warranty_expiry" name="warranty_expiry" class="form-control">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter equipment description"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Equipment</button>
                <a href="{{ route('maintenance.equipment') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    .equipment-form h2 {
        margin-bottom: 1.5rem;
    }

    .form-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 0.35rem;
        background-color: white;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
    }

    textarea.form-control {
        resize: vertical;
    }

    .form-actions {
        grid-column: span 2;
        display: flex;
        justify-content: flex-start;
        gap: 1rem;
        margin-top: 1rem;
    }

    .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        border-radius: 0.35rem;
        border: none;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: #3a59d6;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: var(--gray-200);
        color: var(--gray-700);
    }

    .btn-secondary:hover {
        background-color: var(--gray-300);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .form-container {
            grid-template-columns: 1fr;
        }

        .form-actions {
            grid-column: span 1;
        }
    }
</style>
@endsection
