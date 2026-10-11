@extends('layouts.admin')

@section('title', 'Edit product | Admin')

@section('content')
    <section class="max-w-2xl">
        <a class="text-sm underline" href="{{ route('admin.products.index') }}">Back to products</a>
        <h1 class="mt-3 text-3xl font-semibold">Edit product</h1>
        <form class="mt-7" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.products._form')
            <div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Save changes</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.products.index') }}">Cancel</a></div>
        </form>
    </section>
@endsection