<?php

// This file will contain routes for HR module
// These routes will be uncommented once the Livewire components are created

use Illuminate\Support\Facades\Route;
use App\Livewire\HR\Employees;
use App\Livewire\HR\JobPositions;
use App\Livewire\HR\Attendance;
use App\Livewire\HR\LeaveManagement;
use App\Livewire\HR\Payroll;
use App\Livewire\HR\ShiftManagement;
use App\Livewire\HR\WorkEquipment;
use App\Livewire\HR\Reports;

Route::middleware(['auth'])->group(function () {
    // Employee Management
    Route::get('/hr/employees', Employees::class)->name('hr.employees');
    
    // Job Positions and Categories
    Route::get('/hr/positions', JobPositions::class)->name('hr.positions');
    
    // Attendance Management
    Route::get('/hr/attendance', Attendance::class)->name('hr.attendance');
    
    // Leave Management
    Route::get('/hr/leave', LeaveManagement::class)->name('hr.leave');
    
    // Payroll
    Route::get('/hr/payroll', Payroll::class)->name('hr.payroll');
    
    // Shift Management
    Route::get('/hr/shifts', ShiftManagement::class)->name('hr.shifts');
    
    // Work Equipment Control
    Route::get('/hr/equipment', WorkEquipment::class)->name('hr.equipment');
    
    // Reports Dashboard
    Route::get('/hr/reports', Reports::class)->name('hr.reports');
});
