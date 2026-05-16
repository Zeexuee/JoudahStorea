@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Produk</h1>
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="border rounded px-3 py-2">
            <button class="px-3 py-2 bg-amber-600 text-white rounded">Cari</button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 p-3 text-green-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.products.bulkDiscount') }}">
        @csrf
        <div class="mb-4 flex gap-3 items-center">
            <label class="text-sm">Pilih Produk:</label>
            <select id="bulk-action-select" name="discount_percent" class="border rounded px-3 py-2">
                <option value="">Nonaktifkan Diskon</option>
                @for($i=5;$i<=50;$i+=5)
                    <option value="{{ $i }}">{{ $i }}%</option>
                @endfor
+            </select>
+            <button type="submit" class="px-3 py-2 bg-amber-600 text-white rounded">Terapkan ke Produk Terpilih</button>
         </div>

        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left"><input id="select-all" type="checkbox"></th>
                        <th class="p-3 text-left">Nama</th>
                        <th class="p-3 text-left">Harga</th>
                        <th class="p-3 text-left">Diskon</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($products as $product)
                        <tr>
                            <td class="p-3"><input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox"></td>
                            <td class="p-3">{{ $product->name }}</td>
                            <td class="p-3">{{ Number::currency($product->price, 'IDR') }}</td>
                            <td class="p-3">@if($product->has_discount) {{ $product->discount_percent }}% ({{ Number::currency($product->price_after_discount, 'IDR') }}) @else - @endif</td>
                            <td class="p-3">
                                <a href="{{ route('admin.products.editDiscount', $product) }}" class="text-amber-600">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('select-all')?.addEventListener('change', function(e) {
    const checked = e.target.checked;
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checked);
});
</script>
@endpush
