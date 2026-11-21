@extends('layouts.admin')

@section('title', 'Add product option | Admin')

@section('content')
    <section class="max-w-3xl"><a class="text-sm underline" href="{{ route('admin.products.variants.index', $product) }}">Back to options</a><h1 class="mt-3 text-3xl font-semibold">Add option for {{ $product->name }}</h1><form class="mt-7" method="POST" action="{{ route('admin.products.variants.store', $product) }}">@csrf @include('admin.products.variants._form')<div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Create option</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.products.variants.index', $product) }}">Cancel</a></div></form></section>
@endsection