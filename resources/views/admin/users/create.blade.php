@extends('layouts.admin')

@section('title', 'Create account | Admin')

@section('content')
    <section class="max-w-2xl">
        <a class="text-sm underline" href="{{ route('admin.users.index') }}">Back to accounts</a>
        <h1 class="mt-3 text-3xl font-semibold">Create account</h1>
        <form class="mt-7" method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form')
            <div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Create account</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.users.index') }}">Cancel</a></div>
        </form>
    </section>
@endsection