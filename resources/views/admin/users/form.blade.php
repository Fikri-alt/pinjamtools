@extends('layouts.app')
@section('title',($user->exists ? 'Edit' : 'Tambah').' User - Admin')
@section('content')
<h1 class="text-xl font-bold mb-3">{{ $user->exists ? 'Edit' : 'Tambah' }} User</h1>
<form method="POST" action="{{ $user->exists ? route('admin.users.update',$user->id) : route('admin.users.store') }}" class="bg-white rounded shadow p-4 text-sm space-y-3">
    @csrf
    @if($user->exists) @method('PUT') @endif
    <div class="grid md:grid-cols-2 gap-3">
        <label>Nama <input type="text" name="name" value="{{ old('name',$user->name) }}" class="border rounded px-3 py-1 w-full" required></label>
        <label>Email <input type="email" name="email" value="{{ old('email',$user->email) }}" class="border rounded px-3 py-1 w-full" required></label>
        <label>Password {{ $user->exists ? '(kosongkan jika tidak diubah)' : '' }} <input type="password" name="password" class="border rounded px-3 py-1 w-full" {{ $user->exists ? '' : 'required' }}></label>
        <label>No HP <input type="text" name="no_hp" value="{{ old('no_hp',$user->no_hp) }}" class="border rounded px-3 py-1 w-full"></label>
        <label>Departemen <select name="department_id" class="border rounded px-3 py-1 w-full"><option value="">-- none --</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id',$user->department_id)==$d->id)>{{ $d->nama }}</option>@endforeach</select></label>
        <label>Role @if(!auth()->user()->isSuperAdmin())(hanya super_admin bisa ubah)@endif
            <select name="role" class="border rounded px-3 py-1 w-full" {{ auth()->user()->isSuperAdmin() ? '' : 'disabled' }}>
                @foreach($roles as $r)<option value="{{ $r }}" @selected(old('role',$user->role ?? 'peminjam')===$r)>{{ $r }}</option>@endforeach
            </select>
        </label>
    </div>
    <button class="bg-slate-900 text-white px-4 py-2 rounded">Simpan</button>
</form>
@endsection
