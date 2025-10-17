<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'KeepCloud') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-black min-h-screen flex flex-col">

    {{-- Tampilkan navbar hanya jika user sudah login --}}
    @auth
        <!-- Navbar -->
        <header class="fixed top-0 left-0 right-0 bg-[#1CAFFF] text-black flex justify-between items-center px-8 py-3 shadow-md z-30">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/cloud.png') }}" alt="Logo" class="w-10 h-10">
                <h1 class="text-3xl font-extrabold text-white">KEEPCLOUD</h1>
            </div>

            <!-- Search & Profile -->
            <div class="flex items-center gap-6">
                <div class="relative">
                    <input type="text" placeholder="Cari ........"
                        class="rounded-full pl-4 pr-10 py-2 text-black focus:outline-none border-[3px] border-black w-72 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="absolute right-3 top-2.5 h-5 w-5 text-gray-700"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </div>

                <div class="w-10 h-10 border-[3px] border-black rounded-full flex items-center justify-center bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#1CAFFF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.779.607 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </header>

        <!-- Sidebar -->
    <aside class="fixed top-[72px] left-0 bottom-[40px] w-80 bg-[#1CAFFF] text-black flex flex-col justify-between p-6 shadow-lg z-20 overflow-y-auto rounded-tr-2xl rounded-br-2xl">
        <div>
            <!-- Tombol Menu Sidebar -->
            <div class="space-y-3">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 bg-white rounded-full px-5 py-2 border-[3px] border-black hover:bg-gray-100 transition shadow-md">
                    🏠 <span class="font-medium">Beranda</span>
                </a>

                <a href="#"
                    class="flex items-center gap-3 bg-white rounded-full px-5 py-2 border-[3px] border-black hover:bg-gray-100 transition shadow-md">
                    🕒 <span class="font-medium">Terbaru</span>
                </a>

                <a href="#"
                    class="flex items-center gap-3 bg-white rounded-full px-5 py-2 border-[3px] border-black hover:bg-gray-100 transition shadow-md">
                    ⭐ <span class="font-medium">Berbintang</span>
                </a>

                <a href="{{ route('files.folder') }}"
                    class="flex items-center gap-3 bg-white rounded-full px-5 py-2 border-[3px] border-black hover:bg-gray-100 transition shadow-md">
                    📁 <span class="font-medium">Penyimpanan saya</span>
                </a>

                <a href="#"
                    class="flex items-center gap-3 bg-white rounded-full px-5 py-2 border-[3px] border-black hover:bg-gray-100 transition shadow-md">
                    🗑️ <span class="font-medium">Sampah</span>
                </a>

                <!-- Tombol Buat -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center justify-center gap-2 bg-white border-[3px] border-black rounded-full px-5 py-2 hover:bg-gray-100 transition w-full shadow-md">
                        ➕ <span class="font-medium">Buat</span>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.outside="open = false" x-transition
                        class="absolute left-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg z-10 p-4 space-y-4">

                        <!-- Upload -->
                        <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data"
                            class="flex flex-col gap-2">
                            @csrf
                            <label class="text-sm font-semibold text-gray-700">Upload File</label>
                            <input type="file" name="file" required
                                class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <button
                                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">Upload</button>
                        </form>

                        <hr>

                        <!-- Buat Folder -->
                        <form action="{{ route('folder.create') }}" method="POST" class="flex flex-col gap-2">
                            @csrf
                            <label class="text-sm font-semibold text-gray-700">Buat Folder</label>
                            <input type="text" name="folder_name" placeholder="Nama Folder" required
                                class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <button
                                class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition">Buat</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Storage Bar -->
            <div class="mt-6">
                <div class="text-xs font-medium text-white mb-1">1,92 GB dari 10 GB telah digunakan</div>
                <div class="w-full bg-white rounded-full h-2 border-[2px] border-black">
                    <div class="bg-green-500 h-2 rounded-full" style="width: 19%"></div>
                </div>
            </div>
        </div>

        <!-- Footer Sidebar -->
        <div class="mt-8 border-t border-white/50 pt-4 space-y-3">
            <a href="#"
                class="flex items-center gap-2 bg-white rounded-full px-5 py-2 border-[3px] border-black hover:bg-gray-100 transition shadow-md">
                ⚙️ <span class="font-medium">Pengaturan</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 bg-white rounded-full px-5 py-2 border-[3px] border-black hover:bg-gray-100 transition w-full shadow-md">
                    🚪 <span class="font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </aside>
    
    @endauth

    <!-- Konten utama -->
    <main class="flex-1 flex 
        @auth pt-[72px] @endauth 
        overflow-hidden min-h-screen">
        @yield('content')
    </main>

</body>
</html>
