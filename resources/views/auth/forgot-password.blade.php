<x-guest-layout>
    <div class="mb-4 md:mb-6 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Forgot Password?</h2>
        <p class="text-sm md:text-base text-gray-600">No problem. We'll send you a reset link.</p>
    </div>

    <div class="mb-4 md:mb-6 text-xs md:text-sm text-gray-600 text-center">
        {{ __('Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-lime-400 focus:ring focus:ring-lime-200 focus:ring-opacity-50" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-3 md:gap-4 mt-4 md:mt-6">
            <button type="submit" class="w-full bg-lime-400 text-gray-900 px-6 py-2.5 md:py-3 rounded-full font-semibold hover:bg-lime-500 hover:-translate-y-0.5 transition-all duration-300 shadow-md hover:shadow-lime-400/50 text-sm md:text-base">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>

        <div class="mt-4 md:mt-6 text-center">
            <p class="text-xs md:text-sm text-gray-600">
                Remember your password? 
                <a href="{{ route('login') }}" class="text-lime-600 hover:text-lime-700 font-semibold transition-colors duration-300">
                    Back to login
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
