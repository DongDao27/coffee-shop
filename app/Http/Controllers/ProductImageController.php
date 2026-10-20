<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function index()
    {
        return view('products.images', ['products' => Product::query()->orderBy('id')->paginate(10)]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        $path = $request->file('image')->store('products', 'public');
        $previous = $product->image_path;
        try {
            $product->forceFill(['image_path' => $path, 'display_image_url' => url(Storage::disk('public')->url($path))])->save();
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
        if ($previous && str_starts_with($previous, 'products/') && ! Product::query()->where('image_path', $previous)->exists()) {
            Storage::disk('public')->delete($previous);
        }

        return back()->with('status', __('Image updated.'));
    }
}
