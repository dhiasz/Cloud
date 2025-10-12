<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'KeepCloud') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-black min-h-screen flex flex-col">

    <!-- Navbar -->
    @auth
    <header class="bg-[#1CAFFF] text-black flex justify-between items-center px-6 py-3 shadow-md border-b border-gray-300">
        <div class="flex items-center space-x-3 text-6xl">
            <img src="{{ asset('images/cloud.png') }}" alt="Logo" class="w-12 h-12">
            <h1 class="text-4xl ml-1.5  font-bold text-white">   KEEP CLOUD</h1>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" placeholder="Cari .........." class="rounded-full pl-4 pr-10 py-1 text-black focus:outline-none border border-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-2 h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-red-500 text-black px-4 py-2 rounded-md hover:bg-red-600 transition">Logout</button>
            </form>
        </div>
    </header>
    @endauth

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="bg-[#1CAFFF] w-64 text-black flex flex-col justify-between transition-all duration-300">
            <div class="p-4 space-y-2">
                <button id="toggleSidebar" class="w-full text-left bg-white text-[#1CAFFF] font-semibold py-2 rounded-md hover:bg-gray-100">
                    ☰ Menu
                </button>

                <div class="mt-4 space-y-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 bg-white border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none text-[#1CAFFF]  hover:bg-gray-200 mb-1 drop-shadow-sm mb-1 drop-shadow-sm">
                        <span>🏠</span> <span>Beranda</span>
                    </a>
                    <a href="#" class="flex items-center gap-2 bg-white border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none text-[#1CAFFF]  hover:bg-gray-200 mb-1 drop-shadow-sm">
                        <span>🕒</span> <span>Terbaru</span>
                    </a>
                    <a href="#" class="flex items-center gap-2 bg-white border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none text-[#1CAFFF]  hover:bg-gray-200 mb-1 drop-shadow-sm">
                        <span>⭐</span> <span>Berbintang</span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 bg-white border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none text-[#1CAFFF]  hover:bg-gray-200 mb-1 drop-shadow-sm">
                        <span>📁</span> <span>Penyimpanan Saya</span>
                    </a>
                    <a href="#" class="flex items-center gap-2 bg-white border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none text-[#1CAFFF]  hover:bg-gray-200 mb-1 drop-shadow-sm">
                        <span>🗑️</span> <span>Sampah</span>
                    </a>
                    <a href="#" class="flex items-center gap-2 bg-white border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none text-[#1CAFFF]  hover:bg-gray-200 mb-1 drop-shadow-sm">
                        <span>➕</span> <span>Buat</span>
                    </a>
                </div>

                <div class="mt-6">
                    <div class="text-sm">1.92 GB dari 10 GB digunakan</div>
                    <div class="w-full bg-white rounded-full h-2 mt-1">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 19%"></div>
                    </div>
                </div>
            </div>

            <div class="p-4 space-y-3 border-t border-white">
                <a href="#" class="flex items-center gap-2 bg-white text-[#1CAFFF] border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none hover:bg-gray-200 mb-1 drop-shadow-sm">
                    <span>⚙️</span> <span>Pengaturan</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 bg-white text-[#1CAFFF] border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none hover:bg-gray-200 mb-1 drop-shadow-sm w-full">
                        <span>🚪</span> <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Konten Utama -->
        <main id="mainContent" class="flex-1 bg-white p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <footer class="bg-[#1CAFFF] text-center text-black py-3 text-sm">
        &copy; {{ date('Y') }} KeepCloud — Semua Hak Dilindungi
    </footer>

    <!-- Script toggle sidebar -->
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-64');
        });
    </script>

</body>
</html>
