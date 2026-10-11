@extends('layouts.admin')

@section('title', 'Add service | Admin')

@section('content')
    <section class="max-w-2xl">
        <a class="text-sm underline" href="{{ route('admin.services.index') }}">Back to services</a>
        <h1 class="mt-3 text-3xl font-semibold">Add service</h1>
        <form class="mt-7" method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.services._form')
            <div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Create service</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.services.index') }}">Cancel</a></div>
        </form>
    </section>
@endsection