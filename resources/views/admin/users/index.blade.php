@extends('layouts.admin')

@section('title', 'Accounts | Admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase text-gray-500">Account management</p>
            <h1 class="mt-2 text-3xl font-semibold">Accounts</h1>
        </div>
        <a class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('admin.users.create') }}">Create account</a>
    </div>

    @if (session('status'))<p class="mt-5 border-l-4 border-emerald-700 bg-white px-4 py-3 text-sm" role="status">{{ session('status') }}</p>@endif
    @error('account')<p class="mt-5 border-l-4 border-red-700 bg-white px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror

    <form class="mt-6 flex flex-wrap items-end gap-3 print:hidden" method="GET" action="{{ route('admin.users.index') }}">
        <div>
            <label class="block text-sm font-medium" for="search">Name or email</label>
            <input class="mt-1 rounded border border-gray-300 px-3 py-2" id="search" name="search" value="{{ request('search') }}">
        </div>
        <div>
            <label class="block text-sm font-medium" for="role">Role</label>
            <select class="mt-1 rounded border border-gray-300 px-3 py-2" id="role" name="role">
                <option value="">All roles</option>
                @foreach (['customer' => 'Customer', 'employee' => 'Employee', 'admin' => 'Admin'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="rounded border border-gray-300 px-4 py-2 text-sm font-medium" type="submit">Filter</button>
        <a class="px-2 py-2 text-sm underline" href="{{ route('admin.users.index') }}">Clear</a>
    </form>

    <div class="mt-6 overflow-x-auto border-y border-gray-200 bg-white">
        <table class="w-full min-w-[44rem] text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-500">
                <tr><th class="px-4 py-3 font-medium">Account</th><th class="px-4 py-3 font-medium">Phone</th><th class="px-4 py-3 font-medium">Role</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $managedUser)
                    <tr>
                        <td class="px-4 py-3">{{ $managedUser->name }}<span class="mt-1 block text-gray-500">{{ $managedUser->email }}</span></td>
                        <td class="px-4 py-3">{{ $managedUser->phone ?: '—' }}</td>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::headline($managedUser->role) }}</td>
                        <td class="px-4 py-3">{{ $managedUser->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="px-4 py-3 text-right"><div class="flex justify-end gap-4"><a class="underline" href="{{ route('admin.users.edit', $managedUser) }}">Manage</a>@unless(auth()->user()->is($managedUser))<form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return window.confirm('Delete this account? Accounts with order or inventory history cannot be deleted.')">@csrf @method('DELETE')<button class="text-red-700 underline" type="submit">Delete</button></form>@endunless</div></td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-gray-500" colspan="5">No accounts match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $users->links() }}</div>
@endsection