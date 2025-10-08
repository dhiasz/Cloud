<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Cloud Storage') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navbar (hanya tampil kalau user login) -->
    @auth
        <header class="bg-white shadow p-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">☁️ {{ Auth::user()->name }}'s Cloud</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
            </form>
        </header>
    @endauth

    <!-- Main Content -->
    <main class="flex-1 p-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow p-4 text-center text-gray-500">
        &copy; {{ date('Y') }} My Cloud Storage
    </footer>

</body>
</html>
