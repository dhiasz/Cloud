@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center w-full min-h-screen bg-[#1CAFFF]">
    <div class="bg-white rounded-2xl shadow-lg p-10 w-full max-w-md border-[3px] border-black">
        <h2 class="text-2xl font-bold text-[#1CAFFF] mb-6 text-center">Buat Akun</h2>

        {{-- Tampilkan error jika ada --}}
        @if ($errors->any())
            <div class="mb-4 text-red-600 bg-red-100 p-3 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="email" class="text-gray-700 font-semibold">E-mail</label>
                <input type="email" name="email" placeholder="Masukkan email" required
                    class="w-full mt-1 px-4 py-2 border-[2px] border-gray-300 rounded-full focus:outline-none focus:border-[#1CAFFF]">
            </div>

            <div>
                <label for="name" class="text-gray-700 font-semibold">Nama Lengkap</label>
                <input type="text" name="name" placeholder="Masukkan nama lengkap" required
                    class="w-full mt-1 px-4 py-2 border-[2px] border-gray-300 rounded-full focus:outline-none focus:border-[#1CAFFF]">
            </div>

            <div>
                <label for="password" class="text-gray-700 font-semibold">Buat Kata Sandi</label>
                <input type="password" name="password" placeholder="Masukkan kata sandi" required
                    class="w-full mt-1 px-4 py-2 border-[2px] border-gray-300 rounded-full focus:outline-none focus:border-[#1CAFFF]">
            </div>

            <div>
                <label for="password_confirmation" class="text-gray-700 font-semibold">Ulang Kata Sandi</label>
                <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi" required
                    class="w-full mt-1 px-4 py-2 border-[2px] border-gray-300 rounded-full focus:outline-none focus:border-[#1CAFFF]">
            </div>

            <button type="submit"
                class="mt-4 bg-[#1CAFFF] text-white font-semibold py-2 rounded-full border-[2px] border-black hover:bg-[#0e8cd7] transition">
                Daftar
            </button>
        </form>

        <p class="text-sm text-gray-500 mt-6 text-center">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-[#1CAFFF] font-semibold hover:underline">Masuk</a>
        </p>
    </div>
</div>
@endsection
