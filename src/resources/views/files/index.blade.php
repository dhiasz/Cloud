@extends('layouts.app')

@section('content')
<div class="container mx-auto">

    <!-- Header Upload & Folder -->
    <div class="flex justify-end gap-4 mb-6 bg-white">
        <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
            @csrf
            <input type="file" name="file" required class="border border-gray-300 rounded px-2 py-1">
            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Upload File</button>
        </form>

        <form action="{{ route('folder.create') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="folder_name" placeholder="Nama Folder" class="border border-gray-300 rounded px-2 py-1" required>
            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Buat Folder</button>
        </form>
    </div>

    <!-- Grid Horizontal Konsisten -->
    <div class="bg-white flex overflow-x-auto gap-4 py-2">
        <!-- Folders -->
        @foreach($folders as $folder)
            <div class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2">
                <img src="{{ asset('images/folder.png') }}" class="h-20 w-20 object-contain mb-2" alt="Folder">
                <!-- Area teks tetap tinggi -->
                <p class="truncate w-full text-center text-sm h-5">{{ basename($folder) }}</p>
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
            <div class="flex flex-col items-center justify-between min-w-[160px] min-h-[200px] p-2">
                <img src="{{ asset('images/' . $icon) }}" class="h-20 w-20 object-contain mb-2" alt="{{ $ext }}">
                <!-- Area teks tetap tinggi -->
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

        @if(count($folders) + count($files) === 0)
            <p class="text-gray-500 text-center w-full">Belum ada file atau folder</p>
        @endif
    </div>
</div>
@endsection
