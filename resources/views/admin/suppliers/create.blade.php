@extends('layouts.admin')

@section('title', 'Add supplier | Admin')

@section('content')
    <section class="max-w-xl"><a class="text-sm underline" href="{{ route('admin.suppliers.index') }}">Back to suppliers</a><h1 class="mt-3 text-3xl font-semibold">Add supplier</h1><form class="mt-7" method="POST" action="{{ route('admin.suppliers.store') }}">@csrf @include('admin.suppliers._form')<div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Create supplier</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.suppliers.index') }}">Cancel</a></div></form></section>
@endsection