@extends('layouts.admin')

@section('title', 'Edit supply | Admin')

@section('content')
    <section class="max-w-2xl"><a class="text-sm underline" href="{{ route('admin.inventory.show', $item) }}">Back to supply</a><h1 class="mt-3 text-3xl font-semibold">Edit supply item</h1><p class="mt-2 text-sm text-gray-600">On-hand quantity changes only through a stock movement.</p>
        <form class="mt-7" method="POST" action="{{ route('admin.inventory.update', $item) }}" onsubmit="return window.confirm('Save these supply changes?')">@csrf @method('PUT') @include('admin.inventory._form')<div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Save supply</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.inventory.show', $item) }}">Cancel</a></div></form>
    </section>
@endsection