@extends('layouts.app')

@section('content')
<div class="bg-[#1CAFFF] text-black flex items-center justify-center min-h-screen">
    <div class="bg-white shadow rounded p-10 w-[800px] h-[700px] border border-black focus:right-4 ">
        <div class="flex flex-col items-center">
    <img src="{{ asset('images/cloud.png') }}" 
         alt="Logo"
         class="w-[144px] h-[144px] object-cover rounded-full mb-4 mt-2 ">
    </div>


        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf
            <label for="email" class="text-gray-700 font-medium ">E-mail</label>
            <input type="email" name="email" placeholder="Email" required
            class="border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none  focus:ring-blue-400">
            
            <label for="password" class="text-gray-700 font-medium ">Password</label>
            <input type="password" name="password" placeholder="Password" required
                class="border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none  focus:ring-blue-400">
            
            <button type="submit"
            class="bg-blue-500 border border-black focus:right-4 text-white font-semibold rounded-full py-2 hover:bg-blue-600 transition-colors duration-200">
            Masuk
            </button>

        </form>

        <p class="text-sm text-gray-500 mt-4 text-center">
            Belum punya akun? <a href="{{ route('register') }}" class="text-green-500 hover:underline">Daftar</a>
        </p>
    </div>
</div>
@endsection
