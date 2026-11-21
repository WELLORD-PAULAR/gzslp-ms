@extends('layouts.admin')

@section('title', 'Services | Admin')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">CATALOG</p>
            <h1 class="mt-1 text-3xl font-semibold">Services</h1>
        </div>
        <a class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('admin.services.create') }}">Add service</a>
    </div>

    @if (session('status'))
        <p class="mt-5 border-l-4 border-emerald-700 bg-white px-4 py-3 text-sm">{{ session('status') }}</p>
    @endif

    <div class="mt-6 overflow-x-auto border-y border-gray-200 bg-white">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-500">
                <tr><th class="px-4 py-3 font-medium">Service</th><th class="px-4 py-3 font-medium">Starting price</th><th class="px-4 py-3 font-medium">Visibility</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($services as $service)
                    <tr>
                        <td class="px-4 py-3"><span class="font-medium">{{ $service->name }}</span>@if($service->description)<span class="mt-1 block max-w-lg truncate text-gray-500">{{ $service->description }}</span>@endif</td>
                        <td class="px-4 py-3">{{ $service->starting_price === null ? 'Quote required' : number_format((float) $service->starting_price, 2) }}</td>
                        <td class="px-4 py-3">{{ $service->is_active ? 'Visible' : 'Hidden' }}</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-4"><a class="underline" href="{{ route('admin.services.edit', $service) }}">Edit</a><form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return window.confirm('Delete this service?')">@csrf @method('DELETE')<button class="text-red-700 underline" type="submit">Delete</button></form></div></td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-gray-500" colspan="4">No services yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection