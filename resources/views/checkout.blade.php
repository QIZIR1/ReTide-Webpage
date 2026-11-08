@extends('layouts.app')

@section('title', 'Checkout | Re:Tide Marketplace')

@section('content')
<div class="checkout-page min-h-screen pt-28 pb-20 bg-background text-white font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('marketplace.index') }}" class="text-brand hover:text-brandHover text-sm font-medium flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Marketplace
            </a>
        </div>

        <h1 class="text-3xl font-bold mb-2 text-brandHover">Secure Checkout</h1>
        <p class="text-gray-400 mb-10">You're one step away from making a positive impact.</p>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Left Column: User Info & Shipping -->
            <div class="lg:col-span-7 space-y-8">
                <!-- Contact Info -->
                <div class="bg-surface rounded-2xl p-8 border border-border">
                    <div class="flex items-center mb-6">
                        <div class="bg-brand bg-opacity-20 text-brand w-10 h-10 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Contact Information</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Full Name</label>
                            <input type="text" value="{{ auth()->user()->name }}" readonly class="w-full bg-surfaceHover border border-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand transition-colors cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                            <input type="email" value="{{ auth()->user()->email }}" readonly class="w-full bg-surfaceHover border border-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand transition-colors cursor-not-allowed">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-4"><i class="fas fa-info-circle mr-1"></i> Your information is securely handled and synced with your account.</p>
                </div>

                <!-- Trust Badge Area -->
                <div class="bg-brand/10 rounded-2xl p-6 border border-brand/20 flex items-start space-x-4">
                    <i class="fas fa-shield-alt text-brand text-3xl mt-1"></i>
                    <div>
                        <h3 class="font-semibold text-lg text-white mb-1">Eco-Guaranteed & Secure</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Every purchase directly supports ocean cleanup initiatives. Your payment is securely processed through Midtrans, ensuring bank-grade security for your transaction.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="lg:col-span-5">
                <div class="bg-surface rounded-2xl p-8 border border-border sticky top-32">
                    <h2 class="text-xl font-semibold mb-6 border-b border-border pb-4">Order Summary</h2>

                    <div class="space-y-4 mb-6 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($cart as $id => $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-surfaceActive rounded-lg flex items-center justify-center text-2xl border border-border">
                                    {!! $item['image'] !!}
                                </div>
                                <div>
                                    <h4 class="font-medium text-white">{{ $item['name'] }}</h4>
                                    <p class="text-sm text-gray-400">Qty: {{ $item['quantity'] }}</p>
                                </div>
                            </div>
                            <span class="font-medium text-white">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Promo Coupon -->
                    <div class="border-t border-border pt-4 mb-4">
                        @if($coupon)
                        <div class="flex items-center justify-between p-3 bg-brand/10 border border-brand/30 rounded-xl">
                            <div class="text-sm">
                                <i class="fas fa-ticket-alt text-brand mr-1"></i>
                                <span class="font-semibold text-white">{{ $coupon }}</span>
                                <span class="text-gray-400">applied</span>
                            </div>
                            <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-400 transition-colors">Remove</button>
                            </form>
                        </div>
                        @else
                        <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="coupon_code" placeholder="Promo code" maxlength="50" required
                                class="flex-1 bg-surfaceActive border border-border rounded-xl px-4 py-2.5 text-sm text-white uppercase placeholder:normal-case placeholder-gray-500 focus:outline-none focus:border-brand">
                            <button type="submit" class="px-4 py-2.5 rounded-xl border border-brand text-brand text-sm font-semibold hover:bg-brand hover:text-black transition-colors">Apply</button>
                        </form>
                        @endif

                        @if(session('error'))
                        <p class="text-xs text-red-400 mt-2">{{ session('error') }}</p>
                        @elseif(session('success'))
                        <p class="text-xs text-brand mt-2">{{ session('success') }}</p>
                        @endif
                        @error('coupon_code')
                        <p class="text-xs text-red-400 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-border pt-4 space-y-3">
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($discount > 0)
                        <div class="flex justify-between text-gray-400">
                            <span>Discount ({{ $coupon }})</span>
                            <span class="text-brand">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-gray-400">
                            <span>Eco-Shipping</span>
                            <span class="text-brand">Free</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-white pt-4 border-t border-border mt-4">
                            <span>Total</span>
                            <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if(str_starts_with($snapToken, 'DEMO-') || !config('midtrans.client_key'))
                    <div class="mb-5 p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-xl text-amber-300 text-xs flex items-start gap-3">
                        <i class="fas fa-flask text-amber-400 text-base mt-0.5"></i>
                        <div>
                            <p class="font-bold text-amber-200">Mode Simulasi Pembayaran (Demo)</p>
                            <p class="text-gray-400 mt-0.5">Midtrans API key belum diisi. Anda dapat menguji transaksi secara instan melalui simulator.</p>
                        </div>
                    </div>
                    @endif

                    <button type="button" id="pay-button" class="w-full bg-brand hover:bg-brandHover text-black font-bold text-lg py-4 rounded-xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-[0_4px_20px_rgba(99,207,192,0.3)] flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i> Pay Securely Now
                    </button>

                    <div class="flex justify-center space-x-3 mt-6 text-gray-500">
                        <i class="fab fa-cc-visa text-2xl"></i>
                        <i class="fab fa-cc-mastercard text-2xl"></i>
                        <i class="fas fa-wallet text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Simulasi Pembayaran Demo -->
<div id="demo-payment-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
    <div class="bg-[#121212] border border-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl animate-fade-in">
        <div class="flex items-center justify-between border-b border-gray-800 pb-4 mb-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-brand/20 flex items-center justify-center text-brand">
                    <i class="fas fa-credit-card text-sm"></i>
                </div>
                <h3 class="font-bold text-lg text-white">Simulasi Pembayaran (Demo)</h3>
            </div>
            <button type="button" onclick="closeDemoModal()" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="space-y-4 mb-6">
            <div class="bg-black/40 border border-gray-800 rounded-xl p-4">
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>Order ID:</span>
                    <span class="font-mono text-gray-300">{{ $payment->order_id }}</span>
                </div>
                <div class="flex justify-between text-base font-bold text-white">
                    <span>Total Pembayaran:</span>
                    <span class="text-brand">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Pilih Metode Simulasi</label>
                <div class="p-3 bg-gray-900/80 border border-brand/40 rounded-xl flex items-center justify-between text-sm">
                    <div class="flex items-center gap-3 text-white">
                        <i class="fas fa-qrcode text-brand text-lg"></i>
                        <span>QRIS / GoPay / E-Wallet</span>
                    </div>
                    <span class="text-xs bg-brand/20 text-brand px-2 py-0.5 rounded font-bold">Terpilih</span>
                </div>
                <div class="p-3 bg-gray-900/40 border border-gray-800 rounded-xl flex items-center justify-between text-sm text-gray-400">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-university text-lg"></i>
                        <span>Virtual Account (BCA / BNI / Mandiri)</span>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-400 italic">
                Klik tombol di bawah ini untuk menyelesaikan transaksi secara instan tanpa gateway nyata.
            </p>
        </div>

        <div class="space-y-2">
            <button type="button" onclick="confirmDemoPayment()" id="btn-demo-confirm" class="w-full bg-brand hover:bg-brandHover text-black font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Selesaikan Pembayaran (Simulasi)
            </button>
            <button type="button" onclick="closeDemoModal()" class="w-full bg-transparent hover:bg-gray-800 text-gray-400 hover:text-white py-2.5 rounded-xl transition-all text-sm font-medium">
                Batal
            </button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #111111;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #222222;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #63CFC0;
    }
</style>
@endsection

@section('scripts')
    @php
        $isDemo = str_starts_with($snapToken, 'DEMO-') || !config('midtrans.client_key');
    @endphp

    @if(!$isDemo)
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script type="text/javascript">
            document.getElementById('pay-button').onclick = function() {
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        window.location.href = '{{ route("marketplace.success") }}';
                    },
                    onPending: function(result) {
                        alert('Payment is pending. Please complete your payment.');
                    },
                    onError: function(result) {
                        alert('Payment failed. Please try again.');
                    },
                    onClose: function() {
                        // User closed the popup without finishing payment
                    }
                });
            };
        </script>
    @else
        <script type="text/javascript">
            function openDemoModal() {
                document.getElementById('demo-payment-modal').classList.remove('hidden');
            }

            function closeDemoModal() {
                document.getElementById('demo-payment-modal').classList.add('hidden');
            }

            function confirmDemoPayment() {
                const btn = document.getElementById('btn-demo-confirm');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';

                setTimeout(() => {
                    window.location.href = '{{ route("marketplace.success") }}';
                }, 600);
            }

            document.getElementById('pay-button').onclick = function() {
                openDemoModal();
            };
        </script>
    @endif
@endsection
