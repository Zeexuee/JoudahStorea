@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-12">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Atur Diskon untuk: {{ $product->name }}</h2>

        <form method="POST" action="{{ route('admin.products.updateDiscount', $product) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Persentase Diskon (%)</label>
                <input type="number" name="discount_percent" min="0" max="100" value="{{ old('discount_percent', $product->discount_percent) }}" class="w-40 rounded border-gray-200 p-2">
                <p class="text-sm text-gray-500 mt-2">Isi 0 atau kosongkan untuk menonaktifkan diskon.</p>
                @error('discount_percent') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button class="px-4 py-2 bg-amber-600 text-white rounded">Simpan</button>
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 border rounded">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
