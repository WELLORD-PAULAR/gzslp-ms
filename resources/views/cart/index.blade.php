@extends('layouts.app')

@section('title', 'Cart | Print Studio')

@section('content')
    <section>
        <p class="text-sm font-semibold uppercase text-emerald-800">Your order</p>
        <h1 class="mt-2 text-3xl font-semibold">Shopping cart</h1>
        <p class="mt-5 text-gray-600">Your cart is empty.</p>
        <a class="mt-5 inline-flex rounded border border-gray-300 px-4 py-2 font-medium hover:bg-white" href="{{ route('services') }}">Browse services</a>
    </section>
@endsection