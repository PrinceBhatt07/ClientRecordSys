<x-guest-layout>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="mb-4 text-sm text-gray-600">
                        {{ __('Before getting started, could you verify your email address by clicking on the Button Given Below?') }}
                    </div>
                    <div class="card-body">
                        @if (session('message'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('message') }}
                            </div>
                        @endif
                        <form style="float:right" action="{{ url('/send-verification-email') }}" method="POST">
                            @csrf
                            <x-primary-button>
                               {{ 'Send Verification Email' }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

