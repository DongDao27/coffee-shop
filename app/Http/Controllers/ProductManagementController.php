<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductManagementController extends Controller
{
    public function index()
    {
        return view('products.manage', ['products' => Product::with('category')->orderBy('id')->paginate(10)]);
    }

    public function create()
    {
        return view('products.form', ['product' => new Product(['currency' => 'VND']), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return to_route('manage.products.index')->with('status', 'Đã thêm sản phẩm.');
    }

    public function edit(Product $product)
    {
        return view('products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request));

        return to_route('manage.products.index')->with('status', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product)
    {
        if (CartItem::where('product_id', $product->id)->exists()) {
            return back()->withErrors(['product' => 'Sản phẩm đang được dùng trong giỏ hoặc đơn hàng nên không thể xóa.']);
        }
        $path = $product->image_path;
        $product->delete();
        if ($path && str_starts_with($path, 'products/') && ! Product::where('image_path', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }

        return to_route('manage.products.index')->with('status', 'Đã xóa sản phẩm.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'currency' => ['required', 'string', 'size:3'],
            'display_image_url' => ['nullable', 'url', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        $data['display_image_url'] ??= Product::DEFAULT_IMAGE;

        return $data;
    }
}
