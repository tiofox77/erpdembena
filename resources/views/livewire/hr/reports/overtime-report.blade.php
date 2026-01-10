<div class="min-h-screen bg-gradient-to-br from-slate-50 via-purple-50 to-indigo-50">
    <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-violet-700 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('hr.reports') }}" class="p-2 bg-white/20 backdrop-blur rounded-lg hover:bg-white/30 transition">
                        <i class="fas fa-arrow-left text-white"></i>
                    </a>
                    <div class="p-3 bg-white/20 backdrop-blur rounded-xl">
                        <i class="fas fa-clock text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ trans('messages.overtime_report') }}</h1>
                        <p class="text-purple-100 text-sm">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <select wire:model.live="period" class="px-4 py-2 rounded-lg bg-white/20 border-white/30 text-white text-sm focus:ring-white focus:border-white [&>option]:text-gray-800">
                    <option value="current_month">{{ trans('messages.current_month') }}</option>
                    <option value="last_month">{{ trans('messages.last_month') }}</option>
                    <option value="current_quarter">{{ trans('messages.current_quarter') }}</option>
                    <option value="current_year">{{ trans('messages.current_year') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <p class="text-xs text-gray-500 mb-2">{{ trans('messages.total_records') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_records']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <p class="text-xs text-gray-500 mb-2">{{ trans('messages.total_hours') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_hours'], 1) }}h</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <p class="text-xs text-gray-500 mb-2">{{ trans('messages.regular_hours') }}</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($summary['regular_hours'], 1) }}h</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <p class="text-xs text-gray-500 mb-2">{{ trans('messages.night_shift') }}</p>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($summary['night_shift_hours'], 1) }}h</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <p class="text-xs text-gray-500 mb-2">{{ trans('messages.total_amount') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_amount'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500">AOA</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.type') }}</label>
                    <select wire:model.live="typeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ trans('messages.all') }}</option>
                        <option value="regular">{{ trans('messages.regular') }}</option>
                        <option value="night_shift">{{ trans('messages.night_shift') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.department') }}</label>
                    <select wire:model.live="departmentFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ trans('messages.all_departments') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.employee') }}</label>
                    <input type="text" wire:model.live.debounce="employeeFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="{{ trans('messages.search_employee') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('messages.status') }}</label>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ trans('messages.all') }}</option>
                        <option value="pending">{{ trans('messages.pending') }}</option>
                        <option value="approved">{{ trans('messages.approved') }}</option>
                        <option value="rejected">{{ trans('messages.rejected') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.employee') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ trans('messages.type') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.hours') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ trans('messages.amount') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ trans('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($overtimeRecords as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $record->employee->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($record->is_night_shift)
                                    <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800"><i class="fas fa-moon mr-1"></i>{{ trans('messages.night_shift') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800"><i class="fas fa-clock mr-1"></i>{{ trans('messages.regular') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">{{ number_format($record->hours ?? $record->direct_hours, 1) }}h</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-purple-600">{{ number_format($record->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($record->status === 'approved')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">{{ trans('messages.approved') }}</span>
                                @elseif($record->status === 'rejected')
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">{{ trans('messages.rejected') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ trans('messages.pending') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-4xl text-gray-300"></i>
                                <p class="text-gray-500 mt-2">{{ trans('messages.no_records_found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $overtimeRecords->links() }}</div>
        </div>
    </div>
</div>
