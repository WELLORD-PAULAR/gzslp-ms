@extends('layouts.app')

@section('title', 'Custom print and paper goods | Print Studio')

@section('content')
	<section class="border-b border-gray-200 pb-12 pt-4">
		<p class="text-sm font-semibold uppercase text-emerald-800">Printed for your next idea</p>
		<h1 class="mt-3 max-w-2xl text-4xl font-semibold">Make it yours, from the first draft to the final print.</h1>
		<p class="mt-4 max-w-xl text-lg text-gray-600">Order paper products with your own artwork or choose a ready-made design.</p>
		@auth
			<a class="mt-7 inline-flex rounded bg-emerald-800 px-5 py-3 font-medium text-white hover:bg-emerald-900" href="{{ route('products.index') }}">Browse products</a>
		@else
			<a class="mt-7 inline-flex rounded bg-emerald-800 px-5 py-3 font-medium text-white hover:bg-emerald-900" href="{{ route('login') }}">Log in to browse services</a>
		@endauth
	</section>

	<section class="grid gap-8 py-10 sm:grid-cols-2" aria-label="Ways to order">
		<div>
			<p class="text-sm font-semibold uppercase text-emerald-800">01 / Custom</p>
			<h2 class="mt-2 text-xl font-semibold">Bring your own design</h2>
			<p class="mt-2 text-gray-600">Choose a paper product and provide artwork or a design brief.</p>
		</div>
		<div>
			<p class="text-sm font-semibold uppercase text-emerald-800">02 / Ready-made</p>
			<h2 class="mt-2 text-xl font-semibold">Start with a set design</h2>
			<p class="mt-2 text-gray-600">Browse existing designs and select the print options that suit your order.</p>
		</div>
	</section>

	@isset($authModal)
		<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-950/60 p-4" data-auth-overlay data-close-url="{{ route('home') }}">
			<a class="absolute inset-0" href="{{ route('home') }}" aria-label="Close dialog"></a>
			<section class="relative z-10 my-auto w-full max-w-md rounded-lg bg-white p-6 shadow-2xl sm:p-8" role="dialog" aria-modal="true" aria-labelledby="auth-dialog-title" tabindex="-1">
				<div class="flex items-start justify-between gap-4">
					<div>
						<p class="text-sm font-semibold uppercase text-emerald-800">Print Studio account</p>
						<h2 class="mt-2 text-2xl font-semibold" id="auth-dialog-title">{{ $authModal === 'login' ? 'Welcome back' : 'Create your account' }}</h2>
					</div>
					<a class="rounded px-2 text-2xl leading-none text-gray-500 hover:bg-gray-100 hover:text-gray-900" href="{{ route('home') }}" aria-label="Close dialog">&times;</a>
				</div>

				@include('auth.'.$authModal)
			</section>
		</div>
	@endisset
@endsection
