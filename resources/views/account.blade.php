<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Re:Tide</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: '#63CFC0',
                        surface: '#0d0d0d',
                        panel: '#151515',
                        border: '#2a2a2a',
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #050505; color: #ededed; }
        .input-field {
            background-color: #0d0d0d; border: 1px solid #2a2a2a; border-radius: 8px; padding: 12px 16px;
            color: #fff; width: 100%; transition: border-color 0.2s, background-color 0.2s; font-size: 0.95rem;
        }
        .input-field:focus { outline: none; border-color: #63CFC0; background-color: #111; }
        .input-field:disabled, .input-field[readonly] { background-color: #0a0a0a; color: #666; cursor: not-allowed; border-color: #1a1a1a; }
        .btn-primary {
            background-color: #63CFC0; color: #000; font-weight: 500; border-radius: 8px; padding: 10px 20px;
            transition: all 0.2s; font-size: 0.95rem; display: inline-flex; justify-content: center; align-items: center;
        }
        .btn-primary:hover { background-color: #7ae0d3; transform: translateY(-1px); }
        .btn-danger {
            background-color: rgba(220, 38, 38, 0.1); color: #ef4444; border: 1px solid rgba(220, 38, 38, 0.3);
            font-weight: 500; border-radius: 8px; padding: 10px 20px; transition: all 0.2s; font-size: 0.95rem;
        }
        .btn-danger:hover { background-color: rgba(220, 38, 38, 0.2); }
        .btn-secondary {
            background-color: transparent; color: #a3a3a3; font-weight: 500; transition: color 0.2s;
            font-size: 0.95rem; border: 1px solid #2a2a2a; border-radius: 8px; padding: 10px 20px;
        }
        .btn-secondary:hover { color: #fff; border-color: #444; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #050505; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #222; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #63CFC0; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col custom-scrollbar">

    <!-- Navbar -->
    <header class="fixed w-full z-50 top-0 start-0 border-b border-border bg-[#050505]/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center">
                        <img src="{{ asset('assets/img/ReTide_Logo.png') }}" class="h-8" alt="ReTide Logo" />
                    </a>
                </div>
                <nav class="hidden md:block">
                    <ul class="flex space-x-8 text-sm font-medium">
                        <li><a href="/" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                        <li><a href="/about" class="text-gray-400 hover:text-white transition-colors">About</a></li>
                        <li><a href="/contact" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="/account" class="text-brand transition-colors">Account</a></li>
                        <li><a href="/blog" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="/marketplace" class="text-gray-400 hover:text-white transition-colors">Marketplace</a></li>
                        <li><a href="/donation" class="text-gray-400 hover:text-white transition-colors">Donation</a></li>
                    </ul>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="/account" class="hidden md:block text-brand transition-colors">
                        <i class="fas fa-user-circle text-2xl"></i>
                    </a>
                    <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white focus:outline-none p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-[#121212] border-b border-border absolute w-full transition-all duration-300">
            <ul class="px-4 pt-2 pb-6 space-y-4 text-sm font-medium">
                <li><a href="/" class="block text-gray-400 hover:text-white transition-colors">Home</a></li>
                <li><a href="/about" class="block text-gray-400 hover:text-white transition-colors">About</a></li>
                <li><a href="/contact" class="block text-gray-400 hover:text-white transition-colors">Contact</a></li>
                <li><a href="/account" class="block text-brand transition-colors">Account</a></li>
                <li><a href="/blog" class="block text-gray-400 hover:text-white transition-colors">Blog</a></li>
                <li><a href="/marketplace" class="block text-gray-400 hover:text-white transition-colors">Marketplace</a></li>
                <li><a href="/donation" class="block text-gray-400 hover:text-white transition-colors">Donation</a></li>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-32 pb-24 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full">
        <div class="mb-10">
            <h1 class="text-3xl font-semibold text-white mb-2">Account Settings</h1>
            <p class="text-gray-400">Manage your profile information and security preferences.</p>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 bg-brand/10 border border-brand/30 text-brand rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-4 bg-red-900/20 border border-red-500/30 text-red-400 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Sidebar Navigation -->
            <div class="lg:col-span-3 space-y-1">
                <a href="#profile" class="block px-4 py-2.5 rounded-md bg-panel text-white font-medium text-sm border border-border">General</a>
                <form action="{{ route('logout') }}" method="POST" class="block w-full mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2.5 rounded-md text-gray-400 hover:text-white hover:bg-panel font-medium text-sm transition-colors flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sign Out
                    </button>
                </form>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-9 space-y-10">
                
                <!-- Profile Section -->
                <section id="profile" class="bg-surface border border-border rounded-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-border bg-[#0a0a0a]">
                        <h2 class="text-lg font-medium text-white">Profile Information</h2>
                        <p class="text-sm text-gray-500 mt-1">Update your account's profile information and email address.</p>
                    </div>
                    
                    <div class="p-6">
                        <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <!-- Avatar Upload Section -->
                            <div class="flex items-center space-x-6 pb-4 border-b border-border">
                                <div class="relative">
                                    <img id="avatar-preview" 
                                         src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=63cfc0&color=000&size=128' }}" 
                                         alt="Avatar Preview" 
                                         class="w-20 h-20 rounded-full object-cover border-2 border-[#63cfc0] shadow-md">
                                </div>
                                <div class="space-y-1.5 flex-1">
                                    <label class="block text-sm font-medium text-gray-300">Profile Avatar</label>
                                    <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/webp"
                                        class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#63cfc0]/10 file:text-[#63cfc0] hover:file:bg-[#63cfc0]/20 cursor-pointer">
                                    <p class="text-xs text-gray-500">JPG, PNG, atau WEBP. Maksimal 2MB.</p>
                                    @error('avatar')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-300">Username</label>
                                    <input type="text" name="username" value="{{ old('username', $user->name ?? Auth::user()->name) }}" class="input-field" required>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-300">Email Address</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email ?? Auth::user()->email) }}" class="input-field" readonly>
                                    <p class="text-xs text-gray-500 mt-1">Email address cannot be changed.</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-300">Phone Number</label>
                                    <input type="text" name="notelp" value="{{ old('notelp', $user->phone_number ?? Auth::user()->phone_number ?? '') }}" class="input-field" placeholder="+62 812 3456 7890">
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="btn-primary">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Password Section -->
                <section class="bg-surface border border-border rounded-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-border bg-[#0a0a0a]">
                        <h2 class="text-lg font-medium text-white">Update Password</h2>
                        <p class="text-sm text-gray-500 mt-1">Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                    
                    <div class="p-6">
                        <form action="{{ route('account.update') }}" method="POST" class="space-y-6 max-w-md">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-300">Current Password</label>
                                <input type="password" name="current_password" class="input-field">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-300">New Password</label>
                                <input type="password" name="new_password" class="input-field">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-300">Confirm Password</label>
                                <input type="password" name="new_password_confirmation" class="input-field">
                            </div>
                            
                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="btn-secondary">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Danger Zone -->
                <section class="bg-surface border border-border rounded-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-border bg-[#0a0a0a]">
                        <h2 class="text-lg font-medium text-red-500">Danger Zone</h2>
                        <p class="text-sm text-gray-500 mt-1">Permanently delete your account and all associated data.</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="max-w-md">
                                <p class="text-sm text-gray-400">Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be undone.</p>
                            </div>
                            <form action="{{ route('account.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger ml-4 whitespace-nowrap">
                                    Delete Account
                                </button>
                            </form>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-surface border-t border-border mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:flex md:items-center md:justify-between">
            <div class="flex justify-center md:justify-start mb-4 md:mb-0">
                <span class="text-sm text-gray-500">
                    &copy; 2025 <a href="/" class="hover:text-white transition-colors font-semibold">Re:Tide</a>. All Rights Reserved.
                </span>
            </div>
            <ul class="flex justify-center space-x-6 text-sm font-medium text-gray-500">
                <li><a href="/about" class="hover:text-white transition-colors">About Us</a></li>
                <li><a href="/contact" class="hover:text-white transition-colors">Contact</a></li>
                <li><a href="/terms" class="hover:text-white transition-colors">Terms of Service</a></li>
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

            const avatarInput = document.getElementById('avatar-input');
            const avatarPreview = document.getElementById('avatar-preview');
            if (avatarInput && avatarPreview) {
                avatarInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            avatarPreview.src = event.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>