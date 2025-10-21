<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Keep Cloud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#1CAFFF] text-white flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-xl p-10 w-full max-w-md text-center border border-black  focus:outline focus:outline-[7px] focus:outline-none">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">☁️ My Keep Cloud</h1>
        <p class="text-gray-600 mb-6">Simpan, lihat, dan download file dengan mudah.</p>
        
        <div class="flex flex-col gap-3">
            <a href="{{ route('login') }}" class="bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 border border-black  focus:outline focus:outline-[7px] focus:outline-none ">Login</a>
            <a href="{{ route('register') }}" class="bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 border border-black  focus:outline focus:outline-[7px] focus:outline-none">Register</a>
        </div>
    </div>

</body>
</html>
