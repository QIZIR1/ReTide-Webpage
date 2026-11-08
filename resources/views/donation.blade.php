<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Donation | Re:Tide</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <!-- Lenis by Darkroom Engineering -->
  <script src="https://unpkg.com/lenis@1.3.14/dist/lenis.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/lenis@1.3.15/dist/lenis.css">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
  <script src="{{ asset('assets/js/donation.js') }}" defer></script>
  <script src="{{ URL::asset('assets/js/homepage-animation.js') }}"></script>

  <style>
    body { font-family: 'Inter', sans-serif; }
    
    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #050505; }
    ::-webkit-scrollbar-thumb { background: #222; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #63CFC0; }

    /* Toast */
    .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 1000; }
    .toast-message {
        background-color: #63CFC0; color: black; padding: 12px 24px; border-radius: 8px; margin-top: 8px;
        font-weight: 600; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5); display: flex; align-items: center; gap: 10px;
        animation: slideIn 0.3s ease, fadeOut 0.5s ease 2.5s forwards;
    }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }

    /* Timeline Styles */
    .timeline { position: relative; margin-left: 20px; }
    .timeline::before { content: ''; position: absolute; left: 6px; top: 0; bottom: 0; width: 2px; background-color: #333; }
    .timeline-item { position: relative; display: flex; gap: 20px; margin-bottom: 24px; }
    .timeline-dot { width: 14px; height: 14px; background-color: #63CFC0; border-radius: 50%; margin-left: -2px; margin-top: 6px; flex-shrink: 0; box-shadow: 0 0 0 4px #0f0f0f; z-index: 10;}
  </style>

  <script>
    function showToast(message) {
        if (!document.querySelector(".toast-container")) {
            document.body.insertAdjacentHTML("beforeend", '<div class="toast-container"></div>');
        }
        const toast = document.createElement("div");
        toast.className = "toast-message";
        toast.innerHTML = `<i class="fas fa-check-circle"></i><span>${message}</span>`;
        document.querySelector(".toast-container").appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success')) showToast("{{ session('success') }}"); @endif
        @if(session('error')) showToast("{{ session('error') }}"); @endif
    });
  </script>
</head>

<body class="bg-[#050505] text-white antialiased selection:bg-[#63cfc0] selection:text-black">
  <!-- Navbar -->
  <header class="fixed w-full z-50 top-0 start-0 border-b border-white/5 bg-black/80 backdrop-blur-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-20">
              <div class="flex-shrink-0">
                  <a href="/" class="flex items-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">
                      <img src="{{ asset('assets/img/ReTide_Logo.png') }}" class="h-8" alt="ReTide Logo" />
                  </a>
              </div>
              <nav class="hidden md:block">
                  <ul class="flex space-x-8 text-sm font-medium">
                      <li><a href="/" class="text-gray-400 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Home</a></li>
                      <li><a href="/about" class="text-gray-400 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">About</a></li>
                      <li><a href="/contact" class="text-gray-400 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Contact</a></li>
                      <li><a href="/account" class="text-gray-400 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Account</a></li>
                      <li><a href="/blog" class="text-gray-400 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Blog</a></li>
                      <li><a href="/marketplace" class="text-gray-400 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Marketplace</a></li>
                      <li><a href="/donation" class="text-[#63cfc0] font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Donation</a></li>
                  </ul>
              </nav>
              <div class="flex items-center space-x-4">
                  <a href="/account" class="hidden md:block text-gray-400 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">
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
              <li><a href="/marketplace" class="block text-gray-300 transition-colors">Marketplace</a></li>
              <li><a href="/donation" class="block text-[#63cfc0] hover:text-white transition-colors">Donation</a></li>
          </ul>
      </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-16 min-h-screen">
    <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-12 lg:gap-16 items-start">
      
      <!-- Hero & Widget -->
      <div class="flex flex-col gap-8">
        <div class="space-y-4">
            <h1 class="text-4xl lg:text-5xl font-bold leading-tight tracking-tight text-white">Bantu Kami Melindungi Lautan</h1>
            <p class="text-lg text-gray-400 leading-relaxed max-w-xl">Setiap kontribusi Anda menjadi langkah nyata dalam memulihkan ekosistem dan mendukung komunitas pesisir.</p>
        </div>

        <div class="p-8 rounded-2xl bg-[#63cfc0]/5 border border-[#63cfc0]/15">
          <h3 class="text-sm font-semibold text-[#63cfc0] uppercase tracking-wider mb-2">Total Donasi Terkumpul</h3>
          <p class="text-4xl font-bold text-white mb-5">Rp {{ number_format($totalDonations, 0, ',', '.') }}</p>
          <div class="h-2 w-full bg-black/40 rounded-full overflow-hidden mb-3">
             <div class="h-full bg-[#63cfc0] rounded-full transition-all duration-1000 ease-out" style="width: {{ $progressPercentage ?? 0 }}%;"></div>
          </div>
          <p class="text-sm text-gray-400">Target: Rp {{ number_format($donationGoal ?? 100000000, 0, ',', '.') }} &middot; Bersama kita bisa mencapai lebih banyak.</p>
        </div>
                <div class="p-6 rounded-2xl bg-[#0f0f0f] border border-white/10">
          <h3 class="text-sm font-semibold text-[#63cfc0] uppercase tracking-wider mb-3">Ringkasan Donasi Saya</h3>
          <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Total</p>
              <p class="font-semibold text-white">Rp {{ number_format($summary['total'], 0, ',', '.') }}</p>
            </div>
            <div>
              <p class="text-gray-500">Transaksi</p>
              <p class="font-semibold text-white">{{ $summary['count'] }}</p>
            </div>
            <div>
              <p class="text-gray-500">Terakhir</p>
              <p class="font-semibold text-white">{{ $summary['last'] ? 'Rp '.number_format($summary['last']->amount, 0, ',', '.') : '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Sidebar -->
      <div class="bg-[#0f0f0f] border border-white/10 rounded-2xl p-8 shadow-xl">
        <h2 class="text-2xl font-semibold text-white mb-6">Pilih Nominal Donasi</h2>
        
        <form method="POST" action="{{ route('donation.store') }}" class="flex flex-col gap-5" id="donationForm" novalidate>
            @csrf

            <div class="grid grid-cols-2 gap-3 mb-2">
                <button type="button" class="preset-btn bg-[#1a1a1a] border border-[#333] text-white py-3 px-4 rounded-xl font-medium hover:border-[#63cfc0] hover:bg-[#63cfc0]/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] transition-all" data-amount="25000">Rp 25.000</button>
                <button type="button" class="preset-btn bg-[#1a1a1a] border border-[#333] text-white py-3 px-4 rounded-xl font-medium hover:border-[#63cfc0] hover:bg-[#63cfc0]/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] transition-all" data-amount="50000">Rp 50.000</button>
                <button type="button" class="preset-btn bg-[#1a1a1a] border border-[#333] text-white py-3 px-4 rounded-xl font-medium hover:border-[#63cfc0] hover:bg-[#63cfc0]/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] transition-all" data-amount="100000">Rp 100.000</button>
                <button type="button" class="preset-btn bg-[#63cfc0]/10 border border-[#63cfc0] text-[#63cfc0] py-3 px-4 rounded-xl font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] transition-all" data-amount="custom" aria-current="true">Lainnya</button>
            </div>

            <div class="flex flex-col gap-2" id="custom-amount-wrapper">
                <label for="amount-input" class="text-sm font-medium text-gray-400">Nominal (Rp)</label>
                <input type="number" id="amount-input" name="amount" min="1000" value="{{ old('amount') }}" class="w-full bg-[#121212] border border-[#333] text-white py-3 px-4 rounded-xl focus:outline-none focus:border-[#63cfc0] focus:bg-[#161616] placeholder-gray-600 transition-colors @error('amount') border-red-500 @enderror" placeholder="Masukkan nominal">
                @error('amount')
                    <p class="text-red-500 text-sm mt-1" id="amount-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="name-input" class="text-sm font-medium text-gray-400">Nama Lengkap</label>
                <input type="text" id="name-input" name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full bg-[#121212] border border-[#333] text-white py-3 px-4 rounded-xl focus:outline-none focus:border-[#63cfc0] focus:bg-[#161616] placeholder-gray-600 transition-colors @error('name') border-red-500 @enderror" placeholder="Masukkan nama lengkap">
                @error('name')
                    <p class="text-red-500 text-sm mt-1" id="name-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="email-input" class="text-sm font-medium text-gray-400">Email</label>
                <input type="email" id="email-input" name="email" value="{{ old('email', auth()->user()->email) }}" class="w-full bg-[#1a1a1a] border border-[#333] text-gray-500 py-3 px-4 rounded-xl cursor-not-allowed focus:outline-none @error('email') border-red-500 @enderror" readonly>
                @error('email')
                    <p class="text-red-500 text-sm mt-1" id="email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="message-input" class="text-sm font-medium text-gray-400">Pesan atau Dukungan (Opsional)</label>
                <textarea id="message-input" name="message" rows="3" class="w-full bg-[#121212] border border-[#333] text-white py-3 px-4 rounded-xl focus:outline-none focus:border-[#63cfc0] focus:bg-[#161616] placeholder-gray-600 transition-colors resize-y @error('message') border-red-500 @enderror" placeholder="Tulis harapan Anda...">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-sm mt-1" id="message-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-[#63cfc0] text-black font-semibold py-3.5 rounded-full mt-4 hover:bg-[#7ae0d3] hover:-translate-y-px hover:shadow-lg hover:shadow-[#63cfc0]/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f0f0f] transition-all">
              Donasi Sekarang
            </button>
        </form>
      </div>
    </div>

    <!-- Feed Section -->
    <section class="max-w-3xl mx-auto mt-32">
      <h2 class="text-2xl font-semibold text-white mb-8 text-center">Kontributor Terbaru</h2>
      <div class="flex flex-col gap-4">
        @forelse ($donations as $donation)
          <div class="bg-[#0f0f0f] border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-colors">
            <div class="flex gap-4 items-start">
              <div class="w-12 h-12 rounded-full bg-[#1a1a1a] text-[#63cfc0] flex items-center justify-center font-semibold text-lg shrink-0 border border-[#333]">
                {{ strtoupper(substr($donation->name ?? 'A', 0, 1)) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-center mb-1">
                  <span class="font-semibold text-white truncate">{{ $donation->name ?? 'Anonim' }}</span>
                  <span class="font-medium text-[#63cfc0] whitespace-nowrap ml-4">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span>
                </div>
                @if($donation->message)
                  <p class="text-gray-400 text-sm mb-3">"{{ $donation->message }}"</p>
                @endif
                @if($donation->isPaid())
                <a href="{{ route('donation.receipt', $donation) }}" class="inline-block mb-3 text-xs text-[#63cfc0] hover:text-white">Lihat Receipt</a>
                @endif
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-500">{{ $donation->created_at->diffForHumans() }}</span>
                  <button class="toggle-timeline-link text-[#63cfc0] font-medium hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded transition-colors" data-donation-id="{{ $donation->id }}">Lihat Perjalanan</button>
                </div>
              </div>
            </div>
            <div class="timeline-container hidden mt-6 pt-6 border-t border-white/5" id="timeline-{{ $donation->id }}">
              <div class="timeline-content">
                <!-- Updates will be loaded here -->
              </div>
            </div>
          </div>
        @empty
          <p class="text-center text-gray-500 py-12">Belum ada donasi.</p>
        @endforelse
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="border-t border-white/10 mt-auto bg-[#0a0a0a]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:flex md:items-center md:justify-between">
          <div class="flex justify-center md:justify-start mb-4 md:mb-0">
              <span class="text-sm text-gray-500">
                  &copy; 2025 <a href="/" class="hover:text-white transition-colors font-medium">Re:Tide</a>. All Rights Reserved.
              </span>
          </div>
          <ul class="flex justify-center space-x-6 text-sm font-medium text-gray-500">
              <li><a href="/about" class="hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">About Us</a></li>
              <li><a href="/contact" class="hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Contact</a></li>
              <li><a href="/terms" class="hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#63cfc0] rounded">Terms of Service</a></li>
          </ul>
      </div>
  </footer>

  <script>
    // Timeline Fetching
    document.addEventListener('DOMContentLoaded', () => {
      const toggleBtns = document.querySelectorAll('.toggle-timeline-link');
      
      toggleBtns.forEach(btn => {
        btn.addEventListener('click', async function() {
          const donationId = this.getAttribute('data-donation-id');
          const timelineContainer = document.getElementById('timeline-' + donationId);
          const contentDiv = timelineContainer.querySelector('.timeline-content');
          
          if (timelineContainer.classList.contains('hidden')) {
            timelineContainer.classList.remove('hidden');
            if (!contentDiv.hasAttribute('data-loaded')) {
              contentDiv.innerHTML = '<p class="text-gray-500 text-sm">Memuat pembaruan...</p>';
              try {
                const response = await fetch('/donation/' + donationId + '/updates');
                const data = await response.json();
                
                if (data.length > 0) {
                  let html = '<div class="timeline">';
                  data.forEach(update => {
                    html += `
                      <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content-inner">
                          <h4 class="text-white font-medium mb-1">${update.title}</h4>
                          <p class="text-gray-400 text-sm mb-1">${update.description}</p>
                          <span class="text-gray-600 text-xs">${new Date(update.created_at).toLocaleString('id-ID')}</span>
                        </div>
                      </div>
                    `;
                  });
                  html += '</div>';
                  contentDiv.innerHTML = html;
                } else {
                  contentDiv.innerHTML = '<p class="text-gray-500 text-sm">Belum ada update untuk donasi ini.</p>';
                }
                contentDiv.setAttribute('data-loaded', 'true');
              } catch (e) {
                contentDiv.innerHTML = '<p class="text-red-400 text-sm">Gagal memuat update.</p>';
              }
            }
          } else {
            timelineContainer.classList.add('hidden');
          }
        });
      });
    });

    // Preset Buttons Logic
    document.addEventListener('DOMContentLoaded', function() {
        const presetBtns = document.querySelectorAll('.preset-btn');
        const amountInput = document.getElementById('amount-input');
        const customWrapper = document.getElementById('custom-amount-wrapper');

        const inactiveClasses = ['bg-[#1a1a1a]', 'border-[#333]', 'text-white'];
        const activeClasses = ['bg-[#63cfc0]/10', 'border-[#63cfc0]', 'text-[#63cfc0]'];

        presetBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                presetBtns.forEach(b => {
                    b.removeAttribute('aria-current');
                    b.classList.remove(...activeClasses);
                    b.classList.add(...inactiveClasses);
                });
                
                this.setAttribute('aria-current', 'true');
                this.classList.remove(...inactiveClasses);
                this.classList.add(...activeClasses);

                const val = this.getAttribute('data-amount');
                if (val === 'custom') {
                    customWrapper.style.display = 'flex';
                    amountInput.value = '';
                    amountInput.focus();
                } else {
                    customWrapper.style.display = 'none';
                    amountInput.value = val;
                }
            });
        });

    });

  </script>
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