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
    <script src="{{ URL::asset('assets/js/homepage-animation.js') }}"></script>

    <title>{{ $blogPost->title }} | Blog | Re:Tide</title>
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

    <main class="pt-[140px] pb-24 px-4 max-w-screen-xl mx-auto">
        <a href="{{ route('blog.index') }}" class="inline-flex items-center text-sm font-medium text-gray-400 hover:text-brand mb-8 transition-colors group">
            <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Blog
        </a>

        <article class="max-w-[75ch] mx-auto">
            <header class="mb-10 text-center">
                <div class="flex items-center justify-center text-sm text-brand font-medium mb-4 uppercase tracking-wider">
                    @if($blogPost->created_at)
                        <time datetime="{{ $blogPost->created_at->toIso8601String() }}">{{ $blogPost->created_at->format('F j, Y') }}</time>
                        <span class="mx-2 text-gray-500">&bull;</span>
                    @endif
                    <span class="reading-time"><i class="far fa-clock mr-1"></i>{{ $blogPost->reading_time }} min read</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-8 leading-tight break-words">{{ $blogPost->title }}</h1>
                <div class="w-full h-[400px] md:h-[500px] rounded-2xl overflow-hidden border border-[#2A2A2A] bg-[#1E1E1E]">
                    <img src="{{ $blogPost->image_path }}" class="w-full h-full object-cover" alt="Blog cover image">
                </div>
            </header>

            <div class="text-gray-300 text-lg leading-relaxed whitespace-pre-wrap">
                {!! nl2br(e($blogPost->content)) !!}
            </div>
        </article>
    </main>

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
