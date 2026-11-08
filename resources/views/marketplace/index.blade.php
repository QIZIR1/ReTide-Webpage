<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Style -->
    <link rel="stylesheet" href="{{ URL::asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/marketplace.css') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        brand: '#63CFC0',
                        brandHover: '#7ae0d3',
                        background: '#050505',
                        surface: '#0b0b0b',
                        surfaceHover: '#151515',
                        surfaceElevated: '#111111',
                        surfaceActive: '#1a1a1a',
                        border: '#222222'
                    }
                }
            }
        }
    </script>

    <!-- Libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Lenis by Darkroom Engineering -->
    <script src="https://unpkg.com/lenis@1.3.14/dist/lenis.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lenis@1.3.15/dist/lenis.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Script -->
    <script>
        const sessionCart = @json(session()->get('cart', []));
        window.INITIAL_CART = [];
        for (const [id, item] of Object.entries(sessionCart)) {
            window.INITIAL_CART.push({
                id: id,
                name: item.name,
                price: item.price,
                icon: item.image,
                jumlah: item.quantity
            });
        }
    </script>
    <script src="{{ asset('assets/js/marketplace.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showToast("{{ session('success') }}");
            @endif
            @if(session('error'))
                showToast("{{ session('error') }}", "error");
            @endif
        });
    </script>
    <script src="{{ URL::asset('assets/js/homepage-animation.js') }}"></script>

    <title>Marketplace | Re:Tide</title>
</head>

<body class="bg-background text-white font-sans antialiased selection:bg-brand selection:text-black">
    <!-- Navbar -->
    <header class="fixed w-full z-50 top-0 start-0 border-b border-white/5 bg-black/50 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center">
                        <img src="{{ asset('assets/img/ReTide_Logo.png') }}" class="h-8" alt="ReTide Logo" />
                    </a>
                </div>
                <nav class="hidden md:block">
                    <ul class="flex space-x-8 text-sm font-medium">
                        <li><a href="/" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="/about" class="text-gray-300 hover:text-white transition-colors">About</a></li>
                        <li><a href="/contact" class="text-gray-300 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="/account" class="text-gray-300 hover:text-white transition-colors">Account</a></li>
                        <li><a href="/blog" class="text-gray-300 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="/marketplace" class="text-[#63cfc0] transition-colors">Marketplace</a></li>
                        <li><a href="/donation" class="text-gray-300 hover:text-white transition-colors">Donation</a></li>
                    </ul>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="/account" class="hidden md:block text-gray-300 hover:text-white transition-colors">
                        <i class="fas fa-user-circle text-xl"></i>
                    </a>
                    <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-white focus:outline-none p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-[#121212] border-b border-white/5 absolute w-full transition-all duration-300">
            <ul class="px-4 pt-2 pb-6 space-y-4 text-sm font-medium">
                <li><a href="/" class="block text-gray-300 hover:text-white transition-colors">Home</a></li>
                <li><a href="/about" class="block text-gray-300 hover:text-white transition-colors">About</a></li>
                <li><a href="/contact" class="block text-gray-300 hover:text-white transition-colors">Contact</a></li>
                <li><a href="/account" class="block text-gray-300 hover:text-white transition-colors">Account</a></li>
                <li><a href="/blog" class="block text-gray-300 hover:text-white transition-colors">Blog</a></li>
                <li><a href="/marketplace" class="block text-[#63cfc0] hover:text-white transition-colors">Marketplace</a></li>
                <li><a href="/donation" class="block text-gray-300 hover:text-white transition-colors">Donation</a></li>
            </ul>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 overflow-hidden border-b border-border">
        <div class="absolute inset-0 bg-[url('{{ asset('assets/img/TopDecoration.png') }}')] opacity-5 bg-cover bg-center"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-background"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-10">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 tracking-tight">
                Sustainable Marketplace
            </h1>
            <p class="mt-4 max-w-2xl text-lg md:text-xl text-gray-400 mx-auto">
                Discover premium eco-friendly products crafted from recycled ocean waste. Every purchase directly supports marine conservation initiatives.
            </p>
        </div>
    </section>

    <!-- Floating Cart Button -->
    <button id="cart-btn" class="fixed bottom-8 right-8 z-[60] bg-brand hover:bg-brandHover text-black rounded-full shadow-[0_8px_30px_rgba(99,207,192,0.3)] w-16 h-16 flex items-center justify-center transition-all hover:scale-105">
        <i class="fas fa-shopping-bag text-2xl"></i>
        <span id="cart-count" class="absolute -top-2 -right-2 bg-white text-black text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-lg">0</span>
    </button>

    <!-- Products Grid -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2">Curated Collection</h2>
                <p class="text-gray-400">Transforming waste into worth.</p>
            </div>
        </div>

        <!-- Filter & Sorting -->
        @php
            $hasActiveFilter = $filters['category'] || $filters['min_price'] !== null || $filters['max_price'] !== null || $filters['sort'] !== 'newest';
            $inputClass = 'w-full bg-surface border border-border rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand';
        @endphp
        <form method="GET" action="{{ route('marketplace.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-12" aria-label="Filter products">
            <div>
                <label for="filter-category" class="block text-xs uppercase tracking-wider text-gray-500 mb-1.5">Kategori</label>
                <select id="filter-category" name="category" class="{{ $inputClass }}">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-min-price" class="block text-xs uppercase tracking-wider text-gray-500 mb-1.5">Harga Min (Rp)</label>
                <input id="filter-min-price" type="number" name="min_price" min="0" step="1000" placeholder="0"
                    value="{{ $filters['min_price'] !== null ? (int) $filters['min_price'] : '' }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="filter-max-price" class="block text-xs uppercase tracking-wider text-gray-500 mb-1.5">Harga Maks (Rp)</label>
                <input id="filter-max-price" type="number" name="max_price" min="0" step="1000" placeholder="Tanpa batas"
                    value="{{ $filters['max_price'] !== null ? (int) $filters['max_price'] : '' }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="filter-sort" class="block text-xs uppercase tracking-wider text-gray-500 mb-1.5">Urutkan</label>
                <select id="filter-sort" name="sort" class="{{ $inputClass }}">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-brand hover:bg-brandHover text-black font-semibold rounded-xl px-4 py-2.5 text-sm transition-colors">
                    <i class="fas fa-filter mr-1"></i> Terapkan
                </button>
                @if($hasActiveFilter)
                    <a href="{{ route('marketplace.index') }}" class="px-4 py-2.5 rounded-xl border border-border text-gray-400 hover:text-white text-sm transition-colors">Reset</a>
                @endif
            </div>
        </form>

        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($products as $product)
                    <div class="bg-surface rounded-2xl overflow-hidden border border-border hover:border-brand transition-colors duration-300 group flex flex-col h-full">
                        <div class="relative aspect-[4/3] overflow-hidden bg-surfaceElevated">
                            <img src="{{ $product->image_path }}" class="w-full h-full object-cover transition-opacity duration-700" alt="{{ $product->name }}" loading="lazy">
                            <div class="absolute top-4 left-4 bg-black/60 backdrop-blur-md text-white text-xs px-3 py-1 rounded-full border border-white/10">
                                Eco-Certified
                            </div>
                        </div>
                        
                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-brand transition-colors">{{ $product->name }}</h3>
                            <p class="text-gray-400 text-sm mb-4 line-clamp-2 flex-grow">{{ $product->description }}</p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs mb-4">
                                @if($product->category && isset($categories[$product->category]))
                                    <span class="px-2 py-0.5 rounded-full bg-brand/10 text-brand">{{ $categories[$product->category] }}</span>
                                @endif
                                <span class="{{ $product->stock > 0 ? 'text-gray-500' : 'text-red-400' }}">
                                    {{ $product->stock > 0 ? 'Stock: '.$product->stock : 'Out of stock' }}
                                </span>
                                <span class="text-gray-500">Terjual {{ $product->sold_count }}</span>
                            </div>

                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-border">
                                <span class="text-xl font-bold text-white">Rp {{ number_format($product->price, 0, ',', '.') }}</span>

                                <button class="add-to-cart-btn bg-surfaceActive hover:bg-brand text-white hover:text-black w-10 h-10 rounded-full flex items-center justify-center transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}"
                                    data-stock="{{ $product->stock }}"
                                    data-icon='<img src="{{ $product->image_path }}" class="w-full h-full object-cover rounded">'
                                    title="{{ $product->stock > 0 ? 'Add to Cart' : 'Out of stock' }}"
                                    @disabled($product->stock <= 0)
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-20 bg-surface rounded-2xl border border-border">
                <i class="fas fa-box-open text-6xl text-gray-600 mb-4"></i>
                @if($hasActiveFilter)
                    <h3 class="text-xl font-semibold text-white mb-2">Tidak ada produk yang cocok</h3>
                    <p class="text-gray-400">Coba ubah kategori atau rentang harga.</p>
                @else
                    <h3 class="text-xl font-semibold text-white mb-2">No Products Yet</h3>
                    <p class="text-gray-400">Check back later for our new eco-friendly collection.</p>
                @endif
            </div>
        @endif
    </main>

    <!-- Slide-out Cart -->
    <div id="cart-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] hidden opacity-0 transition-opacity duration-300"></div>
    <section class="cart-section fixed top-0 right-0 h-screen w-full sm:w-[450px] bg-background border-l border-border z-[80] transform translate-x-full transition-transform duration-300 flex flex-col shadow-2xl" id="cart-section">
        <!-- Header -->
        <div class="p-6 border-b border-border flex justify-between items-center bg-surface">
            <h2 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-shopping-bag mr-3 text-brand"></i> Your Cart
            </h2>
            <button id="close-cart" aria-label="Close cart" class="text-gray-400 hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-border">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Items -->
        <div class="cart-items flex-grow overflow-y-auto p-6 space-y-4 custom-scrollbar" id="cart-items">
            <!-- Rendered by JS -->
        </div>
        
        <!-- Footer -->
        <div class="p-6 bg-surface border-t border-border">
            <div class="flex justify-between items-center mb-6">
                <span class="text-gray-400">Total</span>
                <span class="text-2xl font-bold text-white">Rp <span id="total-price">0</span></span>
            </div>
            <button class="checkout-btn w-full bg-brand hover:bg-brandHover text-black font-bold text-lg py-4 rounded-xl transition-all flex justify-center items-center shadow-[0_4px_20px_rgba(99,207,192,0.2)] hover:shadow-[0_4px_25px_rgba(99,207,192,0.4)] hover:-translate-y-1">
                <i class="fas fa-lock mr-2"></i> Checkout Securely
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-surface border-t border-border mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:flex md:items-center md:justify-between">
            <div class="flex justify-center md:justify-start mb-4 md:mb-0">
                <span class="text-sm text-gray-400">
                    &copy; 2025 <a href="/" class="hover:text-white transition-colors font-semibold">Re:Tide</a>. All Rights Reserved.
                </span>
            </div>
            <ul class="flex justify-center space-x-6 text-sm font-medium text-gray-400">
                <li><a href="/about" class="hover:text-white transition-colors">About Us</a></li>
                <li><a href="/contact" class="hover:text-white transition-colors">Contact</a></li>
                <li><a href="/terms" class="hover:text-white transition-colors">Terms of Service</a></li>
            </ul>
        </div>
    </footer>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #050505;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #222;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #63CFC0;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            if(btn && menu) {
                btn.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
