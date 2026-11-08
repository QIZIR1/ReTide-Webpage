<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceProduct;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MarketplaceProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $query = MarketplaceProduct::query()
            ->when($filters['category'], fn ($q, $category) => $q->where('category', $category))
            ->when($filters['min_price'] !== null, fn ($q) => $q->where('price', '>=', $filters['min_price']))
            ->when($filters['max_price'] !== null, fn ($q) => $q->where('price', '<=', $filters['max_price']));

        match ($filters['sort']) {
            'best_selling' => $query->orderByDesc('sold_count')->orderByDesc('created_at'),
            'price_asc' => $query->orderBy('price')->orderByDesc('created_at'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        return view('marketplace.index', [
            'products' => $query->get(),
            'filters' => $filters,
            'categories' => MarketplaceProduct::CATEGORIES,
            'sortOptions' => MarketplaceProduct::SORT_OPTIONS,
        ]);
    }

    /**
     * Read the multi-parameter filter from the query string.
     * Invalid values are ignored instead of failing the page.
     */
    private function resolveFilters(Request $request): array
    {
        $valid = Validator::make($request->query(), [
            'category' => ['string', Rule::in(array_keys(MarketplaceProduct::CATEGORIES))],
            'min_price' => ['numeric', 'min:0'],
            'max_price' => ['numeric', 'min:0'],
            'sort' => ['string', Rule::in(array_keys(MarketplaceProduct::SORT_OPTIONS))],
        ])->valid();

        $minPrice = isset($valid['min_price']) ? (float) $valid['min_price'] : null;
        $maxPrice = isset($valid['max_price']) ? (float) $valid['max_price'] : null;

        // Swap when the range is given in the wrong order
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        return [
            'category' => $valid['category'] ?? null,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $valid['sort'] ?? 'newest',
        ];
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
            'category' => ['nullable', Rule::in(array_keys(MarketplaceProduct::CATEGORIES))],
            'price' => 'required|numeric|min:0|max:99999999.99',
            'stock' => 'nullable|integer|min:0|max:1000000',
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

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'image_path' => $imagePath,
        ];

        // When stock is left empty, the database default is used
        if ($request->filled('stock')) {
            $data['stock'] = (int) $request->stock;
        }

        MarketplaceProduct::create($data);

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
            'category' => ['nullable', Rule::in(array_keys(MarketplaceProduct::CATEGORIES))],
            'price' => 'required|numeric|min:0|max:99999999.99',
            'stock' => 'nullable|integer|min:0|max:1000000',
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
            'category' => $request->category,
            'price' => $request->price,
            'stock' => $request->filled('stock') ? (int) $request->stock : $product->stock,
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
