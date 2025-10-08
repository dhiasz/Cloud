@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white shadow rounded-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login</h2>

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
            <input type="email" name="email" placeholder="Email" required
                class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <input type="password" name="password" placeholder="Password" required
                class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Login</button>
        </form>

        <p class="text-sm text-gray-500 mt-4 text-center">
            Belum punya akun? <a href="{{ route('register') }}" class="text-green-500 hover:underline">Register</a>
        </p>
    </div>
</div>
@endsection
