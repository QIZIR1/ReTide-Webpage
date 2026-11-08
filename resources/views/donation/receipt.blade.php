<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt Donasi | Re:Tide</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#050505] text-white antialiased min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-[#0f0f0f] border border-white/10 rounded-2xl p-8">
    <h1 class="text-2xl font-bold text-[#63cfc0] mb-1">Receipt Donasi</h1>
    <p class="text-gray-500 text-sm mb-6">Re:Tide</p>

    <dl class="space-y-3 text-sm">
      <div class="flex justify-between"><dt class="text-gray-400">Kode</dt><dd class="font-semibold">{{ $donation->receipt_code }}</dd></div>
      <div class="flex justify-between"><dt class="text-gray-400">Donatur</dt><dd>{{ $donation->name }}</dd></div>
      <div class="flex justify-between"><dt class="text-gray-400">Nominal</dt><dd class="text-[#63cfc0] font-semibold">Rp {{ number_format($donation->amount, 0, ',', '.') }}</dd></div>
      <div class="flex justify-between"><dt class="text-gray-400">Tanggal</dt><dd>{{ $donation->created_at->format('d M Y H:i') }}</dd></div>
    </dl>

    <p class="text-gray-400 text-sm mt-6">Terima kasih telah berdonasi untuk lautan yang lebih sehat.</p>
    <a href="{{ route('donation.index') }}" class="inline-block mt-6 text-[#63cfc0] text-sm hover:text-white">&larr; Kembali ke Donasi</a>
  </div>
</body>
</html>