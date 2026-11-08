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

    <title>Add Product | Re:Tide</title>
</head>

<body class="defaultTheme">
    <div style="margin-top: 150px;">
        <h1 class="font-semibold text-[#7ae0d3] text-5xl justify-center text-center">Add Product for Marketplace</h1>
        <p class="text-center" style="margin-top: 16px;">Add new product!</p>
    </div>

    <!-- Back Button-->
    <!-- <div class="flex justify-center mt-7">
        <button type="button" class="bg-[#7ae0d3] text-black font-semibold py-2 px-4 rounded-full">
            <a href="/marketplace">Kembali</a>
        </button>
    </div> -->

    @if(session('success'))
        <div class="bg-transparent text-center py-4 lg:px-4">
            <div class="p-2 bg-green-800 items-center text-green-100 leading-none lg:rounded-full flex lg:inline-flex"
                role="alert">
                <span class="flex rounded-full bg-green-300 uppercase px-2 py-1 text-xs font-bold mr-3">Success</span>
                <span class="font-semibold mr-2 text-left flex-auto">Product berhasil disimpan!</span>
            </div>
        </div>
    @elseif($errors->any())
        <div class="bg-transparent text-center py-4 lg:px-4">
            <div class="p-2 bg-red-800 items-center text-red-100 leading-none lg:rounded-full flex lg:inline-flex"
                role="alert">
                <span class="flex rounded-full bg-red-600 uppercase px-2 py-1 text-xs font-bold mr-3">Error</span>
                <span class="font-semibold mr-2 text-left flex-auto">{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.marketplace.store') }}" method="POST" enctype="multipart/form-data" class="max-w-sm mx-auto
        space-y-4 mt-6" novalidate>
        @csrf
        @method('POST')
        <div>
            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-[#7ae0d3] block w-full px-3.5 py-3 placeholder:text-body"
                placeholder="" required />
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="description" class="block mb-2.5 text-sm font-medium text-heading">Description</label>
            <textarea type="text" id="description" name="description"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-[#7ae0d3] block w-full px-3.5 py-3 shadow-xs placeholder:text-body"
                placeholder="" required>{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="price" class="block mb-2.5 text-sm font-medium text-heading">Price</label>
            <input type="number" id="price" name="price" value="{{ old('price') }}"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-[#7ae0d3] block w-full px-3.5 py-3 placeholder:text-body"
                placeholder="" required />
            @error('price')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="stock" class="block mb-2.5 text-sm font-medium text-heading">Stock</label>
            <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" min="0" step="1"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-[#7ae0d3] block w-full px-3.5 py-3 placeholder:text-body"
                required />
            @error('stock')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="image_path" class="block mb-2.5 text-sm font-medium text-heading">Image</label>
            <input type="file" id="image_path" name="image_path"
                class="bg-black border border-default-medium rounded-lg text-heading text-base focus:ring-brand focus:outline-[#7ae0d3] block w-full px-3.5 py-3 shadow-xs placeholder:text-body"
                placeholder="" />
            @error('image_path')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="button"
            class="text-white bg-red-500 box-border border border-transparent font-medium leading-5 rounded-full text-sm px-4 py-2.5">
            <a href="/marketplace">Cancel</a></button>
        <button type="submit"
            class="text-black bg-[#7ae0d3] box-border border border-transparent font-medium leading-5 rounded-full text-sm px-4 py-2.5">Create</button>
    </form>

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
</body>

</html>
