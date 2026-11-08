<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Style -->
    <link rel="stylesheet" href="{{ URL::asset('assets/css/account.css') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#63CFC0',
                            light: '#7ae0d3'
                        }
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

    <!-- GSAP + ScrollTrigger -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <!-- Custom Scripts -->
    <script src="{{ asset('assets/js/account.js') }}" defer></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="{{ URL::asset('assets/js/homepage-animation.js') }}"></script>

    <title>Edit Blog | Re:Tide</title>
</head>

<body class="defaultTheme">
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
                        <li><a href="/blog" class="text-brand hover:text-white transition-colors">Blog</a></li>
                        <li><a href="/marketplace" class="text-gray-300 transition-colors">Marketplace</a></li>
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
                <li><a href="/blog" class="block text-brand hover:text-white transition-colors">Blog</a></li>
                <li><a href="/marketplace" class="block text-gray-300 transition-colors">Marketplace</a></li>
                <li><a href="/donation" class="block text-gray-300 hover:text-white transition-colors">Donation</a></li>
            </ul>
        </div>
    </header>

    <!-- Edit Blog Post Title -->
    <div class="pt-32 pb-8 max-w-screen-xl mx-auto px-4">
        <h1 class="font-semibold text-brand text-5xl justify-center text-center">Edit Blog Post</h1>
        <p class="text-center mt-4">Edit blog post!</p>
    </div>

    <!-- Back Button-->
    <!-- <div class="flex justify-center mt-7">
        <button type="button" class="bg-[#7ae0d3] text-black font-semibold py-2 px-4 rounded-full">
            <a href="/blog">Kembali</a>
        </button>
    </div> -->

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-transparent text-center py-4 lg:px-4">
            <div class="p-2 bg-green-800 items-center text-green-100 leading-none lg:rounded-full flex lg:inline-flex"
                role="alert">
                <span class="flex rounded-full bg-green-300 uppercase px-2 py-1 text-xs font-bold mr-3">Success</span>
                <span class="font-semibold mr-2 text-left flex-auto">Blog post berhasil disimpan!</span>
            </div>
        </div>
    @endif

    <!-- Error Message -->
    @if($errors->any())
        <div class="bg-transparent text-center py-4 lg:px-4">
            <div class="p-2 bg-red-800 items-center text-red-100 leading-none lg:rounded-full flex lg:inline-flex"
                role="alert">
                <span class="flex rounded-full bg-red-600 uppercase px-2 py-1 text-xs font-bold mr-3">Error</span>
                <span class="font-semibold mr-2 text-left flex-auto">{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <!-- Edit Form -->
    <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data"
        class="max-w-2xl mx-auto space-y-6 mt-6 px-4">
        @csrf
        @method('PUT')
        <!-- Title Form -->
        <div>
            <label for="title" class="block mb-2.5 text-sm font-medium text-heading">Title (Min 5, Max 100)</label>
            <input type="text" id="title" name="title" value="{{ old('title', $blog->title) }}"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-brand block w-full px-3.5 py-3 placeholder:text-body"
                placeholder="" required />
        </div>
        <!-- Content Form -->
        <div>
            <label for="content" class="block mb-2.5 text-sm font-medium text-heading">Content (Min 10, Max 5000)</label>
            <textarea id="content" name="content" rows="6"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-brand block w-full px-3.5 py-3 shadow-xs placeholder:text-body"
                placeholder="" required>{{ old('content', $blog->content) }}</textarea>
        </div>
        <!-- Tags Form -->
        <div>
            <label for="tags" class="block mb-2.5 text-sm font-medium text-heading">Tags (Optional, pisahkan dengan koma, maks 5)</label>
            <input type="text" id="tags" name="tags" value="{{ old('tags', implode(', ', $blog->tags ?? [])) }}"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-brand block w-full px-3.5 py-3 placeholder:text-body"
                placeholder="microplastic, coral rescue, daur ulang" />
            @error('tags')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <!-- Image Form -->
        <div>
            <label for="image_path" class="block mb-2.5 text-sm font-medium text-heading">Image (Optional, image must be
                in .jpeg, .png, or .jpg, max 5MB)</label>
            <input type="file" id="image_path" name="image_path"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-brand block w-full px-3.5 py-3 shadow-xs placeholder:text-body"
                placeholder="" />
        </div>

        <div class="flex justify-end gap-4 pt-4">
            <a href="{{ route('admin.blogs.index') }}"
                class="text-white bg-red-500/10 hover:bg-red-500/20 text-red-500 box-border border border-transparent font-medium leading-5 rounded-full text-sm px-6 py-2.5 inline-block text-center transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="text-black bg-brand hover:bg-brand-light box-border border border-transparent font-medium leading-5 rounded-full text-sm px-8 py-2.5 transition-colors">
                Update
            </button>
        </div>
    </form>

    <!-- Footer -->
    <footer class="bg-neutral-primary-soft border border-default m-4 rounded-xl border-gray-900">
        <div class="w-full mx-auto max-w-screen-xl p-4 md:flex md:items-center md:justify-between">
            <span class="text-sm text-body sm:text-center">© 2025 <a href="http://127.0.0.1:8001/"
                    class="hover:underline">Re:Tide</a>. All Rights Reserved.
            </span>
            <ul class="flex flex-wrap items-center mt-3 text-sm font-medium text-body sm:mt-0">
                <li>
                    <a href="/about" class="hover:underline me-4 md:me-6">About</a>
                </li>
                <li>
                    <a href="/contact" class="hover:underline">Contact</a>
                </li>
            </ul>
        </div>
    </footer>
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