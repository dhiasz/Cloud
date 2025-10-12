@extends('layouts.app')



@section('content')
<div class="flex items-center justify-center min-h-screen bg-[#1CAFFF]">
    <div class="bg-white class=bg-white shadow rounded p-10 w-[800px] h-[700px] border border-black focus:right-4 ">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-start">Buat Akun</h2>

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4">
            @csrf
            
            <label for="email" class="text-gray-700 font-medium ">E-mail</label>
            <input type="email" name="email" placeholder="Email" required
            class="border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none  focus:ring-blue-400">

            <label for="name" class="text-gray-700 font-medium ">Nama Lengkap</label>
            <input type="text" name="name" placeholder="Name" required
            class="border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none  focus:ring-blue-400">
                 
                 
            <label for="password" class="text-gray-700 font-medium ">Buat kata sandi</label>
            <input type="password" name="password" placeholder="Password" required
            class="border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none  focus:ring-blue-400">
            
            <label for="password" class="text-gray-700 font-medium ">Ulang kata sandi</label>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required
                class="border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none  focus:ring-blue-400">
            
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 border border-black focus:right-4 rounded-full px-4 py-2 focus:outline-none hover:bg-blue-600">Register</button>
        </form>

        <p class="text-sm text-gray-500 mt-4 text-center">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Login</a>
        </p>
    </div>
</div>
@endsection
