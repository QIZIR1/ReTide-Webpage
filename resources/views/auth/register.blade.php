<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Re:Tide</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="bg-[#0a0a0a] text-white min-h-screen flex">

    <!-- Left Panel: Branding & Image -->
    <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center bg-black overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('assets/img/Homepage Background.jpg') }}" alt="Ocean Background" class="w-full h-full object-cover opacity-50">
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/50 to-transparent"></div>
        </div>
        
        <div class="relative z-10 p-12 text-center max-w-lg">
            <a href="{{ route('home') }}">
                <img src="{{ asset('assets/img/ReTide_Logo.png') }}" alt="Re:Tide Logo" class="h-16 mx-auto mb-8 drop-shadow-lg hover:scale-105 transition-transform">
            </a>
            <h2 class="text-4xl font-bold text-[#63cfc0] mb-4 tracking-tight leading-tight">Join the movement.</h2>
            <p class="text-gray-300 text-lg leading-relaxed">Create an account to track your impact, participate in community cleanups, and access our sustainable marketplace.</p>
        </div>
    </div>

    <!-- Right Panel: Register Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 relative overflow-y-auto">
        <!-- Back Button -->
        <a href="{{ route('home') }}" class="absolute top-8 left-8 flex items-center text-gray-400 hover:text-[#63cfc0] transition-colors group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="text-sm font-medium">Back to Home</span>
        </a>

        <div class="w-full max-w-md">
            
            <!-- Mobile Logo -->
            <div class="lg:hidden text-center mb-10">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('assets/img/ReTide_Logo.png') }}" alt="Re:Tide Logo" class="h-12 mx-auto">
                </a>
            </div>

            <div class="mb-10">
                <h1 class="text-4xl font-bold tracking-tight mb-2">Create Account</h1>
                <p class="text-gray-400 text-lg">Start your journey with Re:Tide today.</p>
            </div>

            @if($errors->any())
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 text-sm p-4 rounded-xl mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-300">Email Address</label>
                    <input type="email" id="email" name="email"
                        class="w-full bg-[#121212] border border-[#222] text-white rounded-xl p-4 focus:outline-none focus:border-[#63cfc0] focus:ring-1 focus:ring-[#63cfc0] transition-colors placeholder:text-[#555]"
                        placeholder="name@example.com" required>
                    <p class="text-xs text-gray-500 mt-2">We'll never share your email with anyone else.</p>
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                    <input type="password" id="password" name="password"
                        class="w-full bg-[#121212] border border-[#222] text-white rounded-xl p-4 focus:outline-none focus:border-[#63cfc0] focus:ring-1 focus:ring-[#63cfc0] transition-colors placeholder:text-[#555]"
                        placeholder="••••••••" required minlength="8">
                    <p class="text-xs text-gray-500 mt-2">Minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan simbol.</p>
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="w-full bg-[#121212] border border-[#222] text-white rounded-xl p-4 focus:outline-none focus:border-[#63cfc0] focus:ring-1 focus:ring-[#63cfc0] transition-colors placeholder:text-[#555]"
                        placeholder="••••••••" required minlength="8">
                    <p class="text-xs text-gray-500 mt-2">Ulangi password yang telah dimasukkan.</p>
                </div>

                <button type="submit"
                    class="w-full bg-[#63cfc0] hover:bg-white text-black font-semibold py-4 rounded-xl transition-all duration-300 shadow-[0_0_15px_rgba(99,207,192,0.2)] hover:shadow-[0_0_25px_rgba(255,255,255,0.4)] text-lg mt-4">
                    Create Account
                </button>
            </form>

            <p class="mt-10 text-center text-gray-400">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-[#63cfc0] font-medium hover:text-white hover:underline transition-colors decoration-2 underline-offset-4">Sign In</a>
            </p>
        </div>
    </div>

</body>
</html>