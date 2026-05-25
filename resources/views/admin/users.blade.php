@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="card">
    <h3>Daftar Pengguna</h3>
    @if(session('success'))
        <div style="color: var(--accent); margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    <div class="table-container mt-1">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" style="display:inline-flex; align-items:center; gap:0.5rem;">
                            @csrf
                            <label style="display:inline-flex; align-items:center; gap:0.2rem;">
                                <input type="checkbox" name="is_admin" {{ $user->is_admin ? 'checked' : '' }}> Admin
                            </label>
                            
                            <button type="submit" class="action-btn edit"><i class="fa-solid fa-save"></i></button>
                        </form>
                    </td>
                    <td>
                        <span style="padding: 0.2rem 0.5rem; border-radius: var(--radius-full); font-size: 0.8rem; background: {{ $user->is_admin ? 'var(--theme-primary-soft)' : 'var(--theme-muted)' }}; color: {{ $user->is_admin ? 'var(--theme-primary-contrast)' : 'var(--theme-text-muted)' }};">
                            {{ $user->is_admin ? 'Admin' : 'Pengguna' }}
                        </span>
                    </td>
                    <td>
                        @if(auth()->id() !== $user->id)
                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
