<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Drive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navbar -->
    <header class="bg-white shadow p-4 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-800">☁️ {{ Auth::user()->name }}'s Cloud</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
        </form>
    </header>

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <!-- Upload Form -->
        <div class="mb-6">
            @if(session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
            @endif

            <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="file" required class="border border-gray-300 rounded px-2 py-1 w-full">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Upload</button>
            </form>
        </div>

        <!-- Files Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($files as $file)
                <div class="bg-white shadow rounded p-4 flex flex-col items-center text-center">
                    <div class="text-gray-700 font-medium truncate w-full">{{ basename($file) }}</div>
                    <a href="{{ route('download', ['filename' => basename($file)]) }}"
                       class="mt-2 inline-block bg-indigo-500 text-white px-3 py-1 rounded hover:bg-indigo-600">Download</a>
                </div>
            @empty
                <p class="text-gray-500 col-span-full text-center mt-6">Belum ada file di cloud kamu 😇</p>
            @endforelse
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow p-4 text-center text-gray-500">
        &copy; {{ date('Y') }} My Cloud Storage
    </footer>

</body>
</html>
