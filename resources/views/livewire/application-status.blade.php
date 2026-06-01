<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Application Status</h1>
        <p class="text-gray-500 mt-1">Here's the current status of your application.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $application->full_name }}</h2>
                <p class="text-gray-500 text-sm">{{ $application->email }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                {{ match($application->status) {
                    'pending'    => 'bg-yellow-100 text-yellow-800',
                    'approved'   => 'bg-green-100 text-green-800',
                    'denied'     => 'bg-red-100 text-red-800',
                    'waitlisted' => 'bg-blue-100 text-blue-800',
                    default      => 'bg-gray-100 text-gray-800',
                } }}">
                {{ ucfirst($application->status) }}
            </span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="font-medium text-gray-500">Program</dt>
                <dd class="text-gray-900">{{ $application->program->name }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Desired Start Date</dt>
                <dd class="text-gray-900">{{ $application->desired_start_date?->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Submitted</dt>
                <dd class="text-gray-900">{{ $application->created_at->format('M j, Y') }}</dd>
            </div>
            @if ($application->reviewed_at)
            <div>
                <dt class="font-medium text-gray-500">Reviewed</dt>
                <dd class="text-gray-900">{{ $application->reviewed_at->format('M j, Y') }}</dd>
            </div>
            @endif
        </dl>

        @if ($application->status === 'approved' && $application->student?->portal_token)
            <div class="mt-8 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-800 font-medium">Your application has been approved! 🎉</p>
                <p class="text-green-700 text-sm mt-1">You can now access your student payment portal to set up your tuition payment plan.</p>
                <a href="{{ route('portal', $application->student->portal_token) }}"
                   class="inline-block mt-3 bg-green-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                    Go to Payment Portal
                </a>
            </div>
        @elseif ($application->status === 'pending')
            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800 text-sm">Your application is under review. We'll notify you by email once a decision has been made.</p>
            </div>
        @elseif ($application->status === 'denied' && $application->notes)
            <div class="mt-8 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-800 font-medium">Application not approved</p>
                <p class="text-red-700 text-sm mt-1">{{ $application->notes }}</p>
            </div>
        @endif
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('apply') }}" class="text-sm text-amber-600 hover:text-amber-800">Submit another application</a>
    </div>
</div>
