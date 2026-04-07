<x-guest-layout>
    <div class="mb-5 sm:mb-6 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Need Password Help?</h2>
        <p class="text-sm md:text-base text-gray-600">Password recovery is handled by an administrator.</p>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
        <p class="font-semibold">Email reset links are currently unavailable.</p>
        <p class="mt-2">
            Please contact an administrator and ask them to manually reset your password. They will give you a temporary password and you will be asked to change it after signing in.
        </p>
    </div>

    <div class="mt-6 space-y-3 text-sm text-gray-600">
        <p>When you contact an administrator, share the email address you use for this account so they can find the correct profile quickly.</p>
        <p>Once they reset it, sign in with the temporary password and follow the required password change step.</p>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-lime-400 px-6 py-3 text-sm font-semibold text-gray-900 transition hover:-translate-y-0.5 hover:bg-lime-500 hover:shadow-lg hover:shadow-lime-300/40">
            Back to Login
        </a>
    </div>
</x-guest-layout>
