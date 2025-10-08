@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white shadow rounded-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Register</h2>

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
            <input type="text" name="name" placeholder="Name" required
                class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
            <input type="email" name="email" placeholder="Email" required
                class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
            <input type="password" name="password" placeholder="Password" required
                class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required
                class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
            
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Register</button>
        </form>

        <p class="text-sm text-gray-500 mt-4 text-center">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Login</a>
        </p>
    </div>
</div>
@endsection
