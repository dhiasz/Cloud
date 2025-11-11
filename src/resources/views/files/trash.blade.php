@extends('layouts.app')

@section('content')
<script src="//unpkg.com/alpinejs" defer></script>

<div 
    x-data="{
        showMenu: false,
        menuX: 0,
        menuY: 0,
        animateKey: 0,
        folderTransition: false,
        sidebarOpen: false,
        openMenu(e) {
            e.preventDefault();
            this.menuX = e.pageX;
            this.menuY = e.pageY - 40;
            this.showMenu = false;
            this.animateKey++;
            setTimeout(() => this.showMenu = true, 10);
        },
        closeMenu() { this.showMenu = false; },
        enterFolder() {
            this.folderTransition = true;
        }
    }"
    @click="closeMenu"
    @contextmenu.prevent="openMenu($event)"
    class="flex w-full h-screen bg-white text-gray-800 overflow-hidden relative select-none"
>

    {{-- Konten Utama --}}
    <div class="flex-1 ml-20 md:ml-80 overflow-y-auto p-8 relative">
        <div 
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="bg-white rounded-md shadow-md p-8 min-h-[calc(100vh-160px)]"
        >
            <h2 class="text-2xl font-bold text-black mb-6">
                Trash
            </h2>

            @if((isset($folders) && count($folders) > 0) || (isset($files) && count($files) > 0))
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    {{-- Folder --}}
                    @if(isset($folders))
                        @foreach($folders as $folder)
                            <div 
                                class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 cursor-pointer hover:bg-gray-100 rounded-lg transition transform hover:scale-105"
                            >
                                <img src="{{ asset('images/folder.png') }}" class="h-20 w-20 object-contain mb-2" alt="folder">
                                <p class="truncate w-full text-center text-sm h-5">{{ basename($folder) }}</p>
                                <div class="mt-1 flex gap-1 flex-wrap justify-center">
                                    {{-- Tombol Restore dan Delete Permanen --}}
                                    <form action="{{ route('trash.restore.folder') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="folder" value="{{ basename($folder) }}">
                                        <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs">Restore</button>
                                    </form>
                                    <form action="{{ route('trash.delete.folder') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="folder" value="{{ basename($folder) }}">
                                        <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Files --}}
                    @if(isset($files))
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
                            <div class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 hover:bg-gray-100 rounded-lg transition transform hover:scale-105">
                                <img src="{{ asset('images/' . $icon) }}" class="h-20 w-20 object-contain mb-2" alt="{{ $ext }}">
                                <p class="truncate w-full text-center text-sm h-5">{{ basename($file) }}</p>
                                <div class="mt-1 flex gap-1 flex-wrap justify-center">
                                    <form action="{{ route('trash.restore.file') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="file" value="{{ basename($file) }}">
                                        <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs">Restore</button>
                                    </form>
                                    <form action="{{ route('trash.delete.file') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="file" value="{{ basename($file) }}">
                                        <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @else
                <p class="text-gray-500 text-center mt-20">🗑 Trash is empty</p>
            @endif
        </div>
    </div>

    {{-- Context Menu Klik Kanan (nonaktifkan upload di trash) --}}
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
            <div class="px-4 py-2 text-sm text-gray-600 text-center">
                Trash actions unavailable
            </div>
        </div>
    </template>

</div>
@endsection
