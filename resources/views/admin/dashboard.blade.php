@extends('layouts.admin')

@section('title', 'Admin dashboard | Print Studio')

@section('content')
    <section>
        <p class="text-sm font-medium text-gray-500">ADMINISTRATION</p>
        <h1 class="mt-2 text-3xl font-semibold">Shop dashboard</h1>
        <p class="mt-3 text-gray-600">Manage printed products, ready-made designs, and customer orders.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" href="{{ route('admin.products.index') }}">Manage products</a>
            <a class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium" href="{{ route('admin.services.index') }}">Manage services</a>
        </div>
    </section>
@endsection