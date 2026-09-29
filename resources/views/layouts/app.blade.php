<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PinjamTools Corpu')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<nav class="bg-slate-900 text-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <a href="{{ url('/') }}" class="font-bold text-lg">PinjamTools</a>
            <div class="flex gap-4 text-sm">
                <a href="{{ url('/') }}" class="{{ request()->routeIs('beranda') ? 'text-yellow-400 font-semibold' : 'hover:text-yellow-300' }}">Beranda</a>
                <a href="{{ route('katalog') }}" class="{{ request()->routeIs('katalog', 'barang.detail') ? 'text-yellow-400 font-semibold' : 'hover:text-yellow-300' }}">Katalog</a>
                <a href="{{ route('status.form') }}" class="{{ request()->routeIs('status.*') ? 'text-yellow-400 font-semibold' : 'hover:text-yellow-300' }}">Cek Status</a>
                <a href="{{ route('pinjam.step1') }}" class="{{ request()->routeIs('pinjam.*', 'keranjang*') ? 'text-yellow-400 font-semibold' : 'hover:text-yellow-300 font-semibold' }}">Ajukan</a>
            </div>
        </div>
        <div class="flex items-center gap-3 text-sm">
            @auth
                <span class="text-gray-300">Halo, {{ Auth::user()->name }}</span>
                @if(in_array(Auth::user()->role, ['admin','supervisor','super_admin']))
                    <a href="{{ url('/admin') }}" class="px-3 py-1 rounded {{ request()->is('admin*') ? 'bg-yellow-400 text-slate-900 font-semibold ring-2 ring-yellow-200' : 'bg-yellow-500 text-slate-900 hover:bg-yellow-400' }}">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 px-3 py-1 rounded hover:bg-red-500">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-3 py-1 rounded border border-white/40 hover:bg-white/10">Login</a>
                <a href="{{ route('register') }}" class="bg-yellow-500 text-slate-900 px-3 py-1 rounded hover:bg-yellow-400">Register</a>
            @endauth
        </div>
    </div>
</nav>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 py-6">
    @auth
        @if(request()->is('admin*') && in_array(Auth::user()->role, ['admin','supervisor','super_admin']))
            @include('admin._nav')
        @endif
    @endauth
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="bg-slate-900 text-gray-400 text-center text-sm py-4">
    PinjamTools Corpu &copy; {{ date('Y') }} - Sistem Peminjaman Alat
</footer>
</body>
</html>
