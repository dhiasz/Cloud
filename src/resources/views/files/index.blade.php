@extends('layouts.app')

@section('content')
<script src="//unpkg.com/alpinejs" defer></script>

<div 
    x-data="{
        showMenu: false,
        menuX: 0,
        menuY: 0,
        animateKey: 0,
        openMenu(e) {
            if ('{{ $currentFolder ?? '' }}' === '') return; // hanya muncul di dalam folder
            e.preventDefault();
            // posisi sedikit di atas pointer
            this.menuX = e.pageX;
            this.menuY = e.pageY - 40;
            this.showMenu = false; // tutup dulu untuk reset animasi
            this.animateKey++; // ubah key agar x-transition terpicu ulang
            setTimeout(() => this.showMenu = true, 10); // tampilkan ulang dengan animasi
        },
        closeMenu() { this.showMenu = false; }
    }"
    @click="closeMenu"
    @contextmenu.prevent="openMenu($event)"
    class="flex w-full h-full bg-white overflow-hidden relative select-none"
>
    <!-- Konten Utama -->
    <div class="flex-1 ml-80 overflow-y-auto p-8">
        <div class="bg-white rounded-md shadow-md p-8 min-h-[calc(100vh-160px)]">
            <h2 class="text-2xl font-bold text-black mb-6">
                {{ $currentFolder ? basename($currentFolder) : 'Penyimpanan Saya' }}
            </h2>

            @if(count($folders) + count($files) === 0)
                <p class="text-gray-500 text-center mt-20">📁 Folder ini kosong</p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <!-- Folder -->
                @foreach($folders as $folder)
                    <div class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 cursor-pointer hover:bg-gray-100 rounded-lg transition">
                        <a href="{{ route('files.index', ['folder' => trim(($currentFolder ? $currentFolder . '/' : '') . basename($folder))]) }}">
                            <img src="{{ asset('images/folder.png') }}" class="h-20 w-20 object-contain mb-2" alt="folder">
                            <p class="truncate w-full text-center text-sm h-5">{{ basename($folder) }}</p>
                        </a>
                    </div>
                @endforeach

                <!-- Files -->
                @foreach($files as $file)
                    @php
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $icon = match($ext) {
                            'jpg','jpeg','png','gif' => 'image.png',
                            'mp4','mkv','mov','avi' => 'video.png',
                            'pdf' => 'pdf.png',
                            'zip','rar','7z' => 'zip.png',
                            default => 'file.png',
                        };
                    @endphp
                    <div class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 hover:bg-gray-100 rounded-lg transition">
                        <img src="{{ asset('images/' . $icon) }}" class="h-20 w-20 object-contain mb-2" alt="{{ $ext }}">
                        <p class="truncate w-full text-center text-sm h-5">{{ basename($file) }}</p>
                        <div class="mt-1 flex gap-1 flex-wrap justify-center">
                            <a href="{{ route('download', ['filename' => basename($file)]) }}" class="bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600 text-xs">Download</a>
                            @if(in_array($ext, ['jpg','jpeg','png','gif','pdf','mp4','mkv','mov','avi']))
                                <a href="{{ route('preview', ['filename' => basename($file)]) }}" target="_blank" class="bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600 text-xs">Preview</a>
                            @endif
                            <form action="{{ route('file.delete', ['filename' => basename($file)]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Dropdown Klik Kanan dengan animasi muncul ulang -->
    <template x-if="animateKey">
        <div
            x-show="showMenu"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 -translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-6 scale-95"
            :style="`top: ${menuY}px; left: ${menuX}px`"
            class="absolute bg-white border border-gray-300 shadow-2xl rounded-md py-3 w-52 z-50"
            @click.outside="closeMenu"
        >
            <form 
                action="{{ route('files.upload') }}" 
                method="POST" 
                enctype="multipart/form-data" 
                class="flex flex-col items-center px-3 py-2 gap-2"
            >
                @csrf
                <input type="hidden" name="currentFolder" value="{{ $currentFolder }}">
                
                <!-- Tombol Upload -->
                <label class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-md w-full text-center transition">
                    Upload File
                    <input 
                        type="file" 
                        name="file" 
                        class="hidden" 
                        required
                        onchange="this.form.submit()" 
                    >
                </label>
            </form>
        </div>
    </template>
</div>
@endsection
