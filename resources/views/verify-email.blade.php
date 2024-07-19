<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for Verification! A verification link has been sent to the email address you provided during registration.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('logout') }}" class="ml-auto">
            @csrf
            <x-primary-button>
                {{ __('Log Out') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>

