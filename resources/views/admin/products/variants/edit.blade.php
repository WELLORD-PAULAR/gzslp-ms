@extends('layouts.admin')

@section('title', 'Edit product option | Admin')

@section('content')
    <section class="max-w-3xl">
        <a class="text-sm underline" href="{{ route('admin.products.variants.index', $product) }}">Back to options</a>

        <h1 class="mt-3 text-3xl font-semibold">Edit {{ $variant->name }}</h1>

        <p class="mt-2 text-sm text-gray-600">
            {{ $variant->is_default ? 'This is the default option.' : 'Inactive options are retained for order history.' }}
        </p>

        <form
            class="mt-7"
            method="POST"
            action="{{ route('admin.products.variants.update', [$product, $variant]) }}"
            onsubmit="return window.confirm('Save changes to this option?')"
        >
            @csrf
            @method('PUT')

            @include('admin.products.variants._form')

            <div class="mt-6 flex gap-3">
                <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">
                    Save option
                </button>
                <a class="px-3 py-2 text-sm underline" href="{{ route('admin.products.variants.index', $product) }}">
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
