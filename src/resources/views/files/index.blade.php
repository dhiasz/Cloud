@extends('layouts.app')

@section('content')
<script src="//unpkg.com/alpinejs" defer></script>

<div 
    x-data="{
        showMenu: false,
        menuX: 0,
        menuY: 0,
        animateKey: 0,
        dragActive: false,
        folderTransition: false,
        openMenu(e) {
            if ('{{ $currentFolder ?? '' }}' === '') return; // hanya muncul di dalam folder
            e.preventDefault();
            this.menuX = e.pageX;
            this.menuY = e.pageY - 40;
            this.showMenu = false;
            this.animateKey++;
            setTimeout(() => this.showMenu = true, 10);
        },
        closeMenu() { this.showMenu = false; },
        handleDragOver(e) {
            e.preventDefault();
            this.dragActive = true;
        },
        handleDragLeave(e) {
            e.preventDefault();
            this.dragActive = false;
        },
        handleDrop(e) {
            e.preventDefault();
            this.dragActive = false;
            const currentFolder = '{{ $currentFolder ?? '' }}';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('currentFolder', currentFolder); // <--- tetap dikirim walau kosong
            for (const file of files) formData.append('files[]', file);

            fetch('{{ route('files.upload') }}', {
                method: 'POST',
                body: formData
            }).then(() => window.location.reload());
        }

        },
        enterFolder() {
            this.folderTransition = true;
        }
    }"
    @click="closeMenu"
    @contextmenu.prevent="openMenu($event)"
    @dragover="handleDragOver"
    @dragleave="handleDragLeave"
    @drop="handleDrop"
    class="flex w-full h-full bg-white overflow-hidden relative select-none"
>
    <!-- Overlay Drag & Drop -->
    <div 
        x-show="dragActive" 
        class="absolute inset-0 bg-blue-100 bg-opacity-70 flex items-center justify-center z-40 transition-all duration-300"
        x-transition
    >
        <div class="text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-blue-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0l4 4m-4-4l-4 4m13 8v4m0 0h-4m4 0h4m-7-4h3a4 4 0 000-8h-1" />
            </svg>
            <p class="text-lg font-semibold text-blue-600">Drop files here to upload</p>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="flex-1 ml-80 overflow-y-auto p-8 relative">
        <div 
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="bg-white rounded-md shadow-md p-8 min-h-[calc(100vh-160px)]"
        >
            <h2 class="text-2xl font-bold text-black mb-6">
                {{ $currentFolder ? basename($currentFolder) : 'Penyimpanan Saya' }}
            </h2>

            @if(count($folders) + count($files) === 0)
                <p class="text-gray-500 text-center mt-20">📁 Folder ini kosong</p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <!-- Folder -->
                @foreach($folders as $folder)
                    <a 
                        href="{{ route('files.index', ['folder' => trim(($currentFolder ? $currentFolder . '/' : '') . basename($folder))]) }}"
                        @click="enterFolder"
                        class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 cursor-pointer hover:bg-gray-100 rounded-lg transition transform hover:scale-105"
                    >
                        <img src="{{ asset('images/folder.png') }}" class="h-20 w-20 object-contain mb-2" alt="folder">
                        <p class="truncate w-full text-center text-sm h-5">{{ basename($folder) }}</p>
                    </a>
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
                    <div class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 hover:bg-gray-100 rounded-lg transition transform hover:scale-105">
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
                            <input type="hidden" name="currentFolder" value="{{ $currentFolder }}">
                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Delete</button>
                        </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Dropdown Klik Kanan -->
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
                
                <!-- Upload Multiple -->
                <label class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-md w-full text-center transition">
                    Upload Files
                    <input 
                        type="file" 
                        name="files[]" 
                        class="hidden" 
                        multiple 
                        onchange="this.form.submit()" 
                    >
                </label>
            </form>
        </div>
    </template>
</div>
@endsection
