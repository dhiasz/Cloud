@extends('layouts.app')

@section('content')
<script src="//unpkg.com/alpinejs" defer></script>

<div class="flex w-full h-full bg-gray-50 overflow-hidden">
    

    <!-- Konten Utama Scrollable -->
    <div class="flex-1 ml-80 overflow-y-auto p-8">
        <div class="bg-white rounded-md shadow-md p-8 text-center min-h-[calc(100vh-160px)]">
            <h2 class="text-2xl font-bold text-black mb-6">Selamat Datang di KeepCloud</h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        @foreach($folders as $folder)
                <div class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2">
                    <img src="{{ asset('images/folder.png') }}" class="h-20 w-20 object-contain mb-2" alt="folder">
                    <p class="truncate w-full text-center text-sm h-5">{{ basename($folder) }}</p>
                    <div class="mt-1 flex gap-1 flex-wrap justify-center">
                        <a href="{{ url('files?path=' . $folder) }}" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs">Buka</a>
                    </div>
                </div>
            @endforeach
               

                @if(count($folders) + count($files) === 0)
                    <p class="text-gray-500 text-center col-span-full">Belum ada file atau folder</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
