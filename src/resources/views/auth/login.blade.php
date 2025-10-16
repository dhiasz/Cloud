@extends('layouts.app')

@section('content')
<div class="w-full min-h-screen flex items-center justify-center bg-[#1CAFFF]">
    <div class="bg-white shadow-lg rounded-2xl p-10 w-[400px] border border-black">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/cloud.png') }}" 
                 alt="Logo"
                 class="w-[120px] h-[120px] object-cover rounded-full mb-4">
            <h2 class="text-2xl font-bold text-[#1CAFFF]">Masuk ke KeepCloud</h2>
        </div>

        @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm">
                <ul class="list-disc ml-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label for="email" class="text-gray-700 font-medium">E-mail</label>
                <input type="email" name="email" placeholder="Email" required
                    class="w-full border border-black rounded-full px-4 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label for="password" class="text-gray-700 font-medium">Password</label>
                <input type="password" name="password" placeholder="Password" required
                    class="w-full border border-black rounded-full px-4 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <button type="submit"
                class="bg-blue-500 border border-black text-white font-semibold rounded-full py-2 hover:bg-blue-600 transition-colors duration-200">
                Masuk
            </button>
        </form>

        <p class="text-sm text-gray-600 mt-6 text-center">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="text-[#1CAFFF] hover:underline font-medium">Daftar</a>
        </p>
    </div>
</div>
@endsection
