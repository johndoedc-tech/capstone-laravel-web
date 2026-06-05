<x-admin-layout>
    <div class="min-h-full bg-gray-50">
        <div class="p-3 sm:p-6">
            <div class="mb-5">
                <h1 class="text-2xl font-bold text-gray-900">Edit LGU Validator</h1>
                <p class="mt-1 text-sm text-gray-500">Update account access, municipality assignment, and optional barangay scope.</p>
            </div>

            <form method="POST" action="{{ route('admin.lgu-validators.update', $validator) }}" class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                @method('PUT')
                @include('admin.lgu-validators._form', ['submitLabel' => 'Save Changes'])
            </form>
        </div>
    </div>
</x-admin-layout>
