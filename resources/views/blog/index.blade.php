<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Style -->
    <link rel="stylesheet" href="{{ URL::asset('assets/css/styles.css') }}">
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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Lenis by Darkroom Engineering
    <script src="https://unpkg.com/lenis@1.3.14/dist/lenis.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lenis@1.3.15/dist/lenis.css">

     GSAP + ScrollTrigger
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script> 
    -->

    <!-- Flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

    <!-- Custom Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="{{ URL::asset('assets/js/homepage-animation.js') }}"></script>

    <title>Blog | Re:Tide</title>
</head>

<body class="bg-[#050505] text-gray-100 font-['Poppins']">
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

    <div class="pt-[160px] pb-16 max-w-screen-xl mx-auto px-4">
        <h1 class="font-bold text-brand text-5xl md:text-6xl text-center tracking-tight">Our Blog</h1>
        <p class="text-center text-gray-400 mt-6 text-lg max-w-2xl mx-auto">Stories, news, and the latest updates about ocean conservation and our journey towards a sustainable circular economy.</p>
    </div>

    <section class="pb-24 px-4 max-w-screen-md mx-auto">
        <div class="flex flex-col space-y-16">
            @foreach($blog as $blogPosts)
                <a href="{{ route('blog.show', $blogPosts->id) }}" class="group block">
                    <article class="flex flex-col gap-6">
                        <div class="relative w-full aspect-[16/9] overflow-hidden rounded-2xl bg-[#1E1E1E]">
                            <img src="{{ $blogPosts->image_path }}"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="{{ $blogPosts->title }}" loading="lazy">
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="flex items-center text-sm text-brand mb-3 font-medium tracking-wide uppercase">
                                @if($blogPosts->created_at)
                                    <time datetime="{{ $blogPosts->created_at->toIso8601String() }}">{{ $blogPosts->created_at->format('F j, Y') }}</time>
                                    <span class="mx-2 text-gray-500">&bull;</span>
                                @endif
                                <span class="reading-time"><i class="far fa-clock mr-1"></i>{{ $blogPosts->reading_time }} min read</span>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-semibold text-white mb-4 group-hover:text-brand transition-colors leading-tight line-clamp-2 break-words truncate max-w-[24ch]">{{ $blogPosts->title }}</h2>
                            <p class="text-gray-400 text-base md:text-lg mb-6 line-clamp-3 leading-relaxed">{{ $blogPosts->content }}</p>
                            <span class="inline-flex items-center text-sm font-semibold text-white group-hover:text-brand transition-colors">
                                Read Article
                                <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </div>
                    </article>
                </a>
            @endforeach
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
