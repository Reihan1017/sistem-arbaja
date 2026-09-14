@extends('layouts.app')

@php $isEdit = $user->exists; @endphp

@section('title', $isEdit ? 'Edit User' : 'Tambah User')
@section('subtitle', 'Atur akses dan hak role akun')

@section('content')

<form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="staff" @selected(old('role', $user->role ?? 'staff') === 'staff')>Staff / Kasir</option>
                    <option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Admin</option>
                </select>
                <small class="text-muted">Admin bisa hapus data & kelola user. Staff hanya buat/edit/cetak.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label>
                <input type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" {{ $isEdit ? '' : 'required' }}>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Simpan</button>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
</form>
@endsection
