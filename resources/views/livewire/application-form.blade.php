<div>
    @if ($submitted)
        <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
            <div class="text-5xl mb-4">✓</div>
            <h2 class="text-2xl font-bold text-green-800 mb-2">Application Submitted!</h2>
            <p class="text-green-700 mb-6">
                Thank you, {{ $first_name }}. We've received your application and will review it shortly.
                You'll receive a confirmation email at <strong>{{ $email }}</strong>.
            </p>
            <a href="{{ route('application.status', $applicationToken) }}"
               class="inline-block bg-amber-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-amber-700 transition">
                Track Your Application Status
            </a>
        </div>
    @else
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Apply for Enrollment</h1>
            <p class="text-gray-500 mt-1">Complete the form below to begin your enrollment application.</p>
        </div>

        <form wire:submit="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input wire:model="first_name" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    @error('first_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input wire:model="last_name" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    @error('last_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input wire:model="phone" type="tel" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Program of Interest <span class="text-red-500">*</span></label>
                <select wire:model="program_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    <option value="">Select a program…</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }} — ${{ number_format($program->tuition_cost, 0) }}</option>
                    @endforeach
                </select>
                @error('program_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input wire:model="date_of_birth" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    @error('date_of_birth') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desired Start Date</label>
                    <input wire:model="desired_start_date" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    @error('desired_start_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Government-Issued ID</label>
                    <input wire:model="id_document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, or PNG — max 5 MB</p>
                    @error('id_document') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transcripts</label>
                    <input wire:model="transcript" type="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, or PNG — max 5 MB</p>
                    @error('transcript') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="bg-amber-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-amber-700 transition disabled:opacity-50">
                    <span wire:loading.remove>Submit Application</span>
                    <span wire:loading>Submitting…</span>
                </button>
            </div>
        </form>
    @endif
</div>
