@extends('layouts.app')

@section('title', 'Your account | Print Studio')

@section('content')
    <section>
        <p class="text-sm font-medium text-gray-500">CUSTOMER ACCOUNT</p>
        <h1 class="mt-2 text-3xl font-semibold">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-3 text-gray-600">Your shopping, custom print requests, and order history will live here.</p>
    </section>
@endsection