@extends('layouts.app')
@section('title','Users - Admin')
@section('content')
<div class="flex items-center justify-between mb-3">
    <h1 class="text-xl font-bold">Users</h1>
    <a href="{{ route('admin.users.create') }}" class="bg-slate-900 text-white px-3 py-1 rounded text-sm">+ Tambah User</a>
</div>
<div class="bg-white rounded shadow overflow-auto">
<table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="p-2">Nama</th><th>Email</th><th>Role</th><th>Dept</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach($users as $u)
        <tr class="border-b">
            <td class="p-2">{{ $u->name }}</td>
            <td class="p-2">{{ $u->email }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100">{{ $u->role }}</span></td>
            <td class="p-2">{{ $u->department->nama ?? '-' }}</td>
            <td class="p-2 flex gap-2">
                <a href="{{ route('admin.users.edit',$u->id) }}" class="text-emerald-600 underline">Edit</a>
                @if(auth()->user()->isSuperAdmin() && $u->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy',$u->id) }}" onsubmit="return confirm('Hapus user?')">@csrf @method('DELETE')<button class="text-red-600 underline">Hapus</button></form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
