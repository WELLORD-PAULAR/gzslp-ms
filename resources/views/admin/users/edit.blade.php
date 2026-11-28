@extends('layouts.admin')

@section('title', 'Manage account | Admin')

@section('content')
    <section class="max-w-2xl">
        <a class="text-sm underline" href="{{ route('admin.users.index') }}">Back to accounts</a>
        <h1 class="mt-3 text-3xl font-semibold">Manage account</h1>
        <p class="mt-2 text-sm text-gray-600">Created {{ $managedUser->created_at->format('M j, Y') }}. Deactivated accounts and their order history are retained.</p>

        @error('account')
            <p class="mt-5 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>
        @enderror

        <form class="mt-7" method="POST" action="{{ route('admin.users.update', $managedUser) }}" onsubmit="return window.confirm('Save changes to this account?')">
            @csrf
            @method('PUT')
            @include('admin.users._form')

            <div class="mt-6 flex gap-3">
                <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Save account</button>
                <a class="px-3 py-2 text-sm underline" href="{{ route('admin.users.index') }}">Cancel</a>
            </div>
        </form>

        @unless (auth()->user()->is($managedUser))
            <form class="mt-4" method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return window.confirm('Delete this account? Accounts with order or inventory history cannot be deleted.')">
                @csrf
                @method('DELETE')
                <button class="text-sm text-red-700 underline" type="submit">Delete account</button>
            </form>
        @endunless
    </section>
@endsection
