<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function editDiscount(Product $product)
    {
        return view('admin.products.discount', compact('product'));
    }

    public function updateDiscount(Request $request, Product $product)
    {
        $validated = $request->validate([
            'discount_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $product->update([
            'discount_percent' => $validated['discount_percent'] ?? null,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Diskon produk berhasil diperbarui');
    }

    public function index(Request $request)
    {
        $query = Product::query()->orderBy('name');

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function bulkDiscount(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'discount_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $ids = $validated['product_ids'];
        $percent = $validated['discount_percent'] ?? null;

        Product::whereIn('id', $ids)->update(['discount_percent' => $percent]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Diskon diterapkan ke produk yang dipilih.');
    }
}
