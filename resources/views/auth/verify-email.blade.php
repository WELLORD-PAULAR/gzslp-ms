@extends('layouts.app')

@section('title', 'Verify your account | Graphic Zone')

@section('content')
@php
    $user = auth()->user();
    $isStaff = $user->isStaff() && ! $user->isPreviewingStorefront();

    $channels = [
        \App\Models\VerificationCode::CHANNEL_EMAIL => [
            'label' => 'Email address',
            'destination' => $user->email,
            'verified' => $user->hasVerifiedEmail(),
            'hint' => 'We send a six-digit code to your inbox.',
        ],
        \App\Models\VerificationCode::CHANNEL_SMS => [
            'label' => 'Phone number',
            'destination' => $user->phone,
            'verified' => $user->hasVerifiedPhone(),
            'hint' => 'We text a six-digit code to your phone.',
        ],
    ];
@endphp

    <section class="mx-auto max-w-3xl">
        <p class="gz-eyebrow">Account security</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Verify your account</h1>
        <p class="mt-3 text-gray-500">
            Enter the six-digit code sent to your email or phone.
            Codes expire in {{ config('verification.code_ttl_minutes') }} minutes.
        </p>

        @if (session('status'))
            <p class="gz-panel mt-5 border-l-4 border-cyan-500 px-4 py-3 text-sm" role="status">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->any())
            <div class="gz-panel mt-5 border-l-4 border-red-600 px-4 py-3 text-sm text-red-800" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="mt-7 grid gap-5 md:grid-cols-2">
            @foreach ($channels as $channel => $details)
                <section class="gz-panel p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black">{{ $details['label'] }}</h2>
                            <p class="mt-1 text-sm font-semibold text-gray-700">
                                {{ $details['destination'] ?: 'Not provided' }}
                            </p>
                        </div>

                        @if ($details['verified'])
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black uppercase tracking-wider text-emerald-900">
                                Verified
                            </span>
                        @endif
                    </div>

                    @if ($details['verified'])
                        <p class="mt-5 text-sm text-gray-500">
                            This contact is verified. No further action is needed.
                        </p>
                    @elseif (blank($details['destination']))
                        <p class="mt-5 text-sm text-gray-500">
                            Add a {{ $channel === 'email' ? 'email address' : 'phone number' }}
                            in your account settings to enable this channel.
                        </p>
                        <a class="gz-btn gz-btn-outline mt-4"
                           href="{{ route($isStaff ? 'admin.account.edit' : 'account.edit') }}">
                            Edit account details
                        </a>
                    @else
                        <p class="mt-4 text-sm text-gray-500">{{ $details['hint'] }}</p>

                        <form class="mt-5" method="POST" action="{{ route('verification.verify', $channel) }}">
                            @csrf

                            <label class="block text-sm font-semibold" for="code-{{ $channel }}">
                                Verification code
                            </label>

                            <input
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-center text-lg font-black tracking-[.4em]"
                                id="code-{{ $channel }}"
                                name="code"
                                type="text"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                placeholder="000000"
                                required
                            >

                            <button class="gz-btn gz-btn-primary mt-4 w-full" type="submit">
                                Verify {{ $channel === 'email' ? 'email' : 'phone' }}
                            </button>
                        </form>

                        <form class="mt-3" method="POST" action="{{ route('verification.send', $channel) }}">
                            @csrf
                            <button class="text-sm font-bold underline" type="submit">
                                Send a new code
                            </button>
                        </form>
                    @endif
                </section>
            @endforeach
        </div>
    </section>
@endsection
