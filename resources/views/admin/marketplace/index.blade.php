@extends('layouts.admin')

@section('header', 'Marketplace Management')

@section('content')
<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <p class="text-secondary">Manage and review all products listed in the marketplace.</p>
    </div>
    <a href="{{ route('admin.marketplace.create') }}" class="inline-flex items-center bg-primary hover:bg-primary/90 text-black font-bold py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-primary/20">
        <i class="fas fa-plus mr-2 text-sm"></i> Add New Product
    </a>
</div>

<div class="bg-dark-card border border-gray-800 rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-dark-lighter text-secondary text-xs font-bold uppercase tracking-widest border-b border-gray-800">
                    <th class="px-6 py-4">Product</th>
                    <th class="px-6 py-4">Name & Description</th>
                    <th class="px-6 py-4 text-right">Price</th>
                    <th class="px-6 py-4 text-right">Stock</th>
                    <th class="px-6 py-4">Added On</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($products as $product)
                <tr class="hover:bg-gray-800/30 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($product->image_path)
                            <div class="h-16 w-16 overflow-hidden rounded-xl border border-gray-700">
                                <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            </div>
                        @else
                            <div class="h-16 w-16 bg-gray-800 border border-gray-700 rounded-xl flex items-center justify-center text-gray-600">
                                <i class="fas fa-box text-xl"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-white mb-1">{{ $product->name }}</div>
                        <div class="text-xs text-secondary opacity-60 truncate max-w-xs">{{ $product->description ?? 'No description' }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-sm font-bold text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-sm font-bold {{ $product->stock > 0 ? 'text-white' : 'text-red-400' }}">{{ $product->stock }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-secondary">
                        {{ $product->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('admin.marketplace.edit', $product->id) }}" 
                               class="text-xs bg-gray-800 hover:bg-primary/20 text-secondary hover:text-primary border border-gray-700 hover:border-primary/30 px-3 py-1.5 rounded-lg transition-all" title="Edit Product">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.marketplace.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 hover:border-red-500/40 px-3 py-1.5 rounded-lg transition-all" title="Delete Product">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-shopping-bag text-5xl mb-4 opacity-10"></i>
                            <p class="text-lg font-medium mb-1">No products found</p>
                            <p class="text-sm opacity-60 mb-6">List some products to get your marketplace started.</p>
                            <a href="{{ route('admin.marketplace.create') }}" class="text-primary hover:underline font-bold">
                                Add your first product &rarr;
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
