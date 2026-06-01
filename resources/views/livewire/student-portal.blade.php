<div>
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome, {{ $student->first_name }}!</h1>
        <p class="text-gray-500 mt-1">Your student payment portal</p>
    </div>

    @foreach ($student->enrollments as $enrollment)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $enrollment->program->name }}</h2>
                    <p class="text-sm text-gray-500">Enrolled {{ $enrollment->enrolled_at->format('M j, Y') }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($enrollment->status) }}
                </span>
            </div>

            @if ($enrollment->paymentPlan)
                @php
                    $plan = $enrollment->paymentPlan;
                    $paid = $enrollment->payments->where('status', 'paid')->sum('amount');
                    $remaining = $plan->total_amount - $paid;
                    $progress = $plan->total_amount > 0 ? round(($paid / $plan->total_amount) * 100) : 0;
                @endphp

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Tuition Paid</span>
                        <span class="font-medium">${{ number_format($paid, 2) }} of ${{ number_format($plan->total_amount, 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $progress }}% complete &mdash; ${{ number_format($remaining, 2) }} remaining</p>
                </div>

                <h3 class="text-sm font-semibold text-gray-700 mb-3">Payment Schedule</h3>
                <div class="space-y-2">
                    @foreach ($enrollment->payments->sortBy('due_date') as $payment)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Due {{ $payment->due_date->format('M j, Y') }}</p>
                                @if ($payment->paid_at)
                                    <p class="text-xs text-gray-500">Paid {{ $payment->paid_at->format('M j, Y') }} via {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold">${{ number_format($payment->amount, 2) }}</span>
                                @if ($payment->status === 'paid')
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Paid</span>
                                @elseif (in_array($payment->status, ['pending', 'overdue']))
                                    <button wire:click="$set('payingPaymentId', {{ $payment->id }})"
                                            class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full hover:bg-amber-200 transition font-medium">
                                        {{ $payment->status === 'overdue' ? 'Pay Overdue' : 'Pay Now' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-amber-800 text-sm font-medium">No payment plan set up yet.</p>
                    <p class="text-amber-700 text-sm mt-1">Program tuition: <strong>${{ number_format($enrollment->program->tuition_cost, 2) }}</strong></p>
                    <button wire:click="$set('showPlanForm', true)"
                            class="mt-3 bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-700 transition">
                        Set Up Payment Plan
                    </button>
                </div>
            @endif
        </div>
    @endforeach

    {{-- Payment plan setup modal --}}
    @if ($showPlanForm)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Set Up Payment Plan</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Tuition Amount ($)</label>
                        <input wire:model="total_amount" type="number" step="0.01" min="1"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        @error('total_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Installments</label>
                        <input wire:model="installment_count" type="number" min="1" max="60"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        @error('installment_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Frequency</label>
                        <select wire:model="frequency" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                            <option value="monthly">Monthly</option>
                            <option value="biweekly">Bi-Weekly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Payment Date</label>
                        <input wire:model="start_date" type="date"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if ($total_amount && $installment_count)
                        <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700">
                            Estimated per payment: <strong>${{ number_format((float)$total_amount / max(1, (int)$installment_count), 2) }}</strong>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex gap-3">
                    <button wire:click="setupPlan"
                            class="flex-1 bg-amber-600 text-white py-2 rounded-lg font-medium hover:bg-amber-700 transition">
                        Create Plan
                    </button>
                    <button wire:click="$set('showPlanForm', false)"
                            class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Pay now modal --}}
    @if ($payingPaymentId)
        @php $payingPayment = $student->enrollments->flatMap->payments->firstWhere('id', $payingPaymentId); @endphp
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Make Payment</h3>
                <p class="text-gray-500 text-sm mb-4">Due {{ $payingPayment?->due_date->format('M j, Y') }}</p>

                <div class="bg-amber-50 rounded-lg p-4 text-center mb-4">
                    <p class="text-3xl font-bold text-amber-700">${{ number_format($payingPayment?->amount ?? 0, 2) }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select wire:model="payment_method" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        <option value="card">Credit / Debit Card</option>
                        <option value="bank_transfer">Bank Transfer (ACH)</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                    </select>
                </div>

                <p class="text-xs text-gray-400 mb-4 text-center">🔒 Demo mode — payments are simulated and always succeed.</p>

                <div class="flex gap-3">
                    <button wire:click="pay({{ $payingPaymentId }})"
                            class="flex-1 bg-green-600 text-white py-2 rounded-lg font-medium hover:bg-green-700 transition">
                        Confirm Payment
                    </button>
                    <button wire:click="$set('payingPaymentId', null)"
                            class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
