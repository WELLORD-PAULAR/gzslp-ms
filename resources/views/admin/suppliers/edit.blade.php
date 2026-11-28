@extends('layouts.admin')

@section('title', 'Edit supplier | Admin')

@section('content')
<<<<<<< HEAD
    <section class="max-w-xl"><a class="text-sm underline" href="{{ route('admin.suppliers.index') }}">Back to suppliers</a><h1 class="mt-3 text-3xl font-semibold">Edit supplier</h1><p class="mt-2 text-sm text-gray-600">Inactive suppliers remain linked to historical stock receipts.</p><form class="mt-7" method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" onsubmit="return window.confirm('Save these supplier changes?')">@csrf @method('PUT') @include('admin.suppliers._form')<div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Save supplier</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.suppliers.index') }}">Cancel</a></div></form></section>
=======
    <section class="max-w-xl"><a class="text-sm underline" href="{{ route('admin.suppliers.index') }}">Back to suppliers</a><h1 class="mt-3 text-3xl font-semibold">Edit supplier</h1><p class="mt-2 text-sm text-gray-600">Inactive suppliers remain linked to historical stock receipts.</p><form class="mt-7" method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" onsubmit="return window.confirm('Save changes to this supplier?')">@csrf @method('PUT') @include('admin.suppliers._form')<div class="mt-6 flex gap-3"><button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white" type="submit">Save supplier</button><a class="px-3 py-2 text-sm underline" href="{{ route('admin.suppliers.index') }}">Cancel</a></div></form></section>
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
@endsection