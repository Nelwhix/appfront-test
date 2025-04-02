<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Jobs\SendPriceChangeNotification;
use App\Models\Product;

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(20);

        return view('admin.products', compact('products'));
    }

    public function create()
    {
        return view('admin.add_product');
    }

    public function store(ProductRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('uploads', ['disk' => 'uploads']);
        } else {
            $validated['image'] = 'product-placeholder.jpg';
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product added successfully');
    }


    public function edit(string $id)
    {
        $product = Product::findOrFail($id);

        return view('admin.edit_product', compact('product'));
    }

    public function update(ProductRequest $request, string $id)
    {
        $validated = $request->validated();
        $product = Product::findOrFail($id);

        // Store the old price before updating
        $oldPrice = $product->price;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', ['disk' => 'uploads']);
            $validated['image'] = $path;
        }

        $product->update($validated);

        // Check if price has changed
        if ($oldPrice !== $product->price) {
            SendPriceChangeNotification::dispatch(
                $product,
                $oldPrice,
                $product->price,
                config('mail.price_notification_email')
            );
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(string $id)
    {
        Product::where('id', $id)->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }
}
