<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceProduct;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;

class MarketplaceProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = MarketplaceProduct::orderBy('created_at', 'desc')->get();

        return view('marketplace.index', ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('marketplace.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:5|max:100',
            'description' => 'required|min:5|max:3000',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_path')) {
            try {
                $imagePath = cloudinary()->uploadApi()->upload($request->file('image_path')->getRealPath(), ['folder' => 'marketplace_products'])['secure_url'];
            } catch (\Throwable $e) {
                $path = $request->file('image_path')->store('marketplace_products', 'public');
                $imagePath = asset('storage/'.$path);
            }
        }

        MarketplaceProduct::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.marketplace.index')->with('success', 'Product berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = MarketplaceProduct::findOrFail($id);

        return view('marketplace.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = MarketplaceProduct::findOrFail($id);

        return view('marketplace.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = MarketplaceProduct::findOrFail($id);

        $request->validate([
            'name' => 'required|min:5|max:100',
            'description' => 'required|min:5|max:3000',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $product->image_path;

        if ($request->hasFile('image_path')) {
            try {
                $imagePath = cloudinary()->uploadApi()->upload($request->file('image_path')->getRealPath(), ['folder' => 'marketplace_products'])['secure_url'];
            } catch (\Throwable $e) {
                $path = $request->file('image_path')->store('marketplace_products', 'public');
                $imagePath = asset('storage/'.$path);
            }
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.marketplace.index')->with('success', 'Product berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = MarketplaceProduct::findOrFail($id);
        $productId = $product->id;
        $productName = $product->name;
        $productPrice = $product->price;

        $product->delete();

        SecurityAuditService::log('PRODUCT_DELETE', [
            'product_id' => $productId,
            'name' => $productName,
            'price' => $productPrice,
        ]);

        return redirect()->route('admin.marketplace.index')->with('success', 'Product berhasil dihapus!');
    }
}
