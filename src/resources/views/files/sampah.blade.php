@extends('layouts.app')

@section('content')
<script src="//unpkg.com/alpinejs" defer></script>

@php
    use Illuminate\Support\Facades\Auth;
    $userPrefix = 'users/' . Auth::id() . '/';
@endphp

<div 
    x-data="trashIndex()" 
    x-init="init()"
    @click="closeMenu"
    @contextmenu.prevent="openMenu($event)"
    class="flex w-full h-full bg-white overflow-hidden relative select-none"
>
    <!-- Context menu for trash is absolute (rendered later) -->

    <!-- Konten Utama -->
    <div class="flex-1 ml-80 overflow-y-auto p-8 relative">
        <div 
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="bg-white rounded-md shadow-md p-8 min-h-[calc(100vh-160px)]"
        >
            <h2 class="text-2xl font-bold text-black mb-6">
                Sampah
            </h2>

            @if(count($folders) + count($files) === 0)
                <p class="text-gray-500 text-center mt-20">🗑️ Sampah kosong</p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                {{-- Folders in trash --}}
                @foreach($folders as $folder)
                    @php
                        $folderName = basename($folder);
                        $relativeFolderPath = trim($folder, '/');
                    @endphp

                    <div 
                        class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 hover:bg-gray-100 rounded-lg transition transform hover:scale-105 cursor-pointer"
                        data-path="{{ $relativeFolderPath }}"
                        data-type="folder"
                        data-name="{{ $folderName }}"
                    >
                        <img src="{{ asset('images/folder.png') }}" class="h-20 w-20 object-contain mb-2" alt="folder">
                        <p class="truncate w-full text-center text-sm h-5">{{ $folderName }}</p>

                        <div class="mt-1 flex gap-1 flex-wrap justify-center">
                            <!-- Restore form (fallback) -->
                            <form action="{{ url('/keepcloud/restore/' . urlencode($relativeFolderPath)) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs">Restore</button>
                            </form>

                            <!-- Permanent delete -->
                            <form action="{{ url('/keepcloud/trash/delete/' . urlencode($relativeFolderPath)) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Hapus Permanen</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Files in trash --}}
                @foreach($files as $file)
                    @php
                        $filenameOnly = basename($file);
                        $relativeFilePath = trim($file, '/');
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $icon = match($ext) {
                            'jpg','jpeg','png','gif' => 'image.png',
                            'mp4','mkv','mov','avi' => 'video.png',
                            'pdf' => 'pdf.png',
                            'zip','rar','7z' => 'zip.png',
                            default => 'file.png',
                        };
                    @endphp

                    <div 
                        class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2 hover:bg-gray-100 rounded-lg transition transform hover:scale-105 cursor-pointer"
                        data-path="{{ $relativeFilePath }}"
                        data-type="file"
                        data-name="{{ $filenameOnly }}"
                    >
                        <img src="{{ asset('images/' . $icon) }}" class="h-20 w-20 object-contain mb-2" alt="{{ $ext }}">
                        <p class="truncate w-full text-center text-sm h-5">{{ $filenameOnly }}</p>

                        <div class="mt-1 flex gap-1 flex-wrap justify-center">
                            <!-- Preview (optional) - will attempt to preview via preview route (works if controller handles trash preview or streams) -->
                            @if(in_array($ext, ['jpg','jpeg','png','gif','pdf','mp4','mkv','mov','avi']))
                                <a href="{{ url('/keepcloud/preview/' . urlencode($relativeFilePath)) }}" target="_blank" class="bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600 text-xs">Preview</a>
                            @endif

                            <!-- Restore -->
                            <form action="{{ url('/keepcloud/restore/' . urlencode($relativeFilePath)) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs">Restore</button>
                            </form>

                            <!-- Permanent delete -->
                            <form action="{{ url('/keepcloud/trash/delete/' . urlencode($relativeFilePath)) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs">Hapus Permanen</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Context Menu (right-click) --}}
    <div
        x-show="showMenu"
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
        :style="`top: ${menuY}px; left: ${menuX}px`"
        class="absolute bg-white border border-gray-300 shadow-2xl rounded-md py-3 w-56 z-50"
        @click.outside="closeMenu"
        style="display: none;"
    >
        <div class="px-3 w-full">
            <div class="text-xs text-gray-600 mb-2">Aksi untuk <strong x-text="selectedName"></strong></div>

            <button
                type="button"
                x-show="selectedType === 'file' || selectedType === 'folder'"
                @click="restoreSelected()"
                class="w-full bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600 mb-2"
            >
                Restore
            </button>

            <button
                type="button"
                x-show="selectedType === 'file' || selectedType === 'folder'"
                @click="forceDeleteSelected()"
                class="w-full bg-red-500 text-white px-3 py-2 rounded text-sm hover:bg-red-600"
            >
                Hapus Permanen
            </button>
        </div>
    </div>
</div>

<script>
function trashIndex() {
    return {
        showMenu: false,
        menuX: 0,
        menuY: 0,
        selectedPath: null,
        selectedName: null,
        selectedType: null,

        init() {
            // nothing for now
        },

        openMenu(e) {
            // detect target item (file/folder) nearest to click
            const targetEl = e.target.closest('[data-path]');
            if (targetEl) {
                this.selectedPath = targetEl.getAttribute('data-path');
                this.selectedName = targetEl.getAttribute('data-name');
                this.selectedType = targetEl.getAttribute('data-type');
            } else {
                this.selectedPath = null;
                this.selectedName = null;
                this.selectedType = null;
            }

            // posisi menu
            const OFFSET_X = 8;
            const OFFSET_Y = -20;
            let x = e.pageX + OFFSET_X;
            let y = e.pageY + OFFSET_Y;

            // koreksi agar tidak keluar viewport
            const maxX = window.scrollX + window.innerWidth;
            const maxY = window.scrollY + window.innerHeight;
            const menuWidth = 224;
            const menuHeightEstimate = 160;

            if (x + menuWidth > maxX) {
                x = Math.max(10, maxX - menuWidth - 10);
            }
            if (y + menuHeightEstimate > maxY) {
                y = Math.max(10, maxY - menuHeightEstimate - 10);
            }
            if (x < 10) x = 10;
            if (y < 10) y = 10;

            this.menuX = x;
            this.menuY = y;
            this.showMenu = false;
            setTimeout(() => { this.showMenu = true; }, 6);
        },

        closeMenu() {
            this.showMenu = false;
            this.selectedPath = null;
            this.selectedName = null;
            this.selectedType = null;
        },

        restoreSelected() {
            if (!this.selectedPath) return;
            if (!confirm('Pulihkan "' + this.selectedName + '" ?')) return;

            const url = "{{ url('/keepcloud/restore') }}/" + encodeURIComponent(this.selectedPath);
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');

            fetch(url, {
                method: 'POST',
                body: fd
            }).then(() => window.location.reload())
            .catch(() => window.location.reload());
        },

        forceDeleteSelected() {
            if (!this.selectedPath) return;
            if (!confirm('Hapus permanen "' + this.selectedName + '" ?')) return;

            const url = "{{ url('/keepcloud/trash/delete') }}/" + encodeURIComponent(this.selectedPath);
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('_method', 'DELETE');

            fetch(url, {
                method: 'POST',
                body: fd
            }).then(() => window.location.reload())
            .catch(() => window.location.reload());
        }
    }
}
</script>
@endsection
