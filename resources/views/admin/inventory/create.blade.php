@extends('layouts.admin')

@section('title', 'Add supply | Admin')

@section('content')
    <section class="max-w-2xl"><a class="text-sm underline" href="{{ route('admin.inventory.index') }}">Back to inventory</a><h1 class="mt-3 text-3xl font-semibold">Add supply item</h1>
        <form class="mt-7" method="POST" action="{{ route('admin.inventory.store') }}">@csrf @include('admin.inventory._form')<div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Create supply</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.inventory.index') }}">Cancel</a></div></form>
    </section>
@endsection