<div> 
    @if (session()->has('message'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>{{ session('message') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1 fw-semibold">
                        <i class="bi bi-people me-2 text-primary"></i>Pengguna
                    </h5>
                    <div class="text-muted small">Manajemen data civitas dan hak akses sistem Lentera.</div>
                </div>
                <button wire:click="create" class="btn btn-primary px-4 shadow-sm" @if($isAdding || $isEditing) disabled @endif>
                    <i class="bi bi-plus-lg me-2"></i>Tambah
                </button>
            </div>
        </div>
    </div>
    @if($isAdding || $isEditing)
    <div class="card border-0 shadow-sm mb-4 border-start border-4 {{ $isAdding ? 'border-success' : 'border-primary' }}">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4 text-uppercase small tracking-wider">
                {{ $isAdding ? 'Form Tambah Pengguna Baru' : 'Form Edit Data Pengguna' }}
            </h6>
            <form wire:submit="{{ $isAdding ? 'store' : 'update' }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Identifier (NIM/NIP)</label>
                        <input type="text" wire:model="form.identifier" class="form-control @error('form.identifier') is-invalid @enderror">
                        @error('form.identifier') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                        <input type="text" wire:model="form.name" class="form-control @error('form.name') is-invalid @enderror">
                        @error('form.name') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Role</label>
                        <select wire:model="form.role" class="form-select @error('form.role') is-invalid @enderror">
                            <option value="">Pilih...</option>
                            <option value="admin">Admin</option>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen</option>
                            <option value="staff">Staff</option>
                        </select>
                        @error('form.role') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>
                    @if($isEditing)
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Status</label>
                        <select wire:model="form.status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Password</label>
                        <input type="password" wire:model="form.password" class="form-control @error('form.password') is-invalid @enderror">
                        @error('form.password') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <button type="button" wire:click="cancel" class="btn btn-light border px-4">Batal</button>
                        <button type="submit" class="btn text-white {{ $isAdding ? 'btn-success' : 'btn-primary' }} px-4">
                            <span wire:loading.remove wire:target="store, update">Simpan</span>
                            <span wire:loading wire:target="store, update">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0 py-3 small fw-bold text-muted" style="width: 50px;">NO</th>
                        <th class="ps-4 border-0 py-3 small fw-bold text-muted">IDENTIFIER</th>
                        <th class="border-0 py-3 small fw-bold text-muted">NAMA</th>
                        <th class="border-0 py-3 small fw-bold text-muted">ROLE</th>
                        <th class="border-0 py-3 small fw-bold text-muted">STATUS</th>
                        <th class="border-0 py-3 small fw-bold text-muted text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr wire:key="{{ $user->id }}">
                        <td class="ps-4 fw-medium text-muted">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="ps-4 fw-medium">{{ $user->identifier }}</td>
                        <td>{{ $user->name }}</td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1 small">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusBadge = [
                                    'active' => 'bg-success',
                                    'pending' => 'bg-warning text-dark',
                                    'inactive' => 'bg-danger'
                                ][$user->status];
                            @endphp
                            <span class="badge {{ $statusBadge }} rounded-pill" style="font-size: 0.7rem;">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button wire:click="confirmDelete({{ $user->id }})" class="btn btn-sm btn-outline-danger border-0" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted small italic">Tidak ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top-0 py-3">
            {{ $users->links() }}
        </div>
    </div>

    <x-confirmation-modal id="deleteUserModal" title="Konfirmasi Hapus" type="danger">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-1 me-3"></i>
            <div>
                <p class="mb-1 fw-bold">Yakin ingin menghapus?</p>
                <p class="text-muted small mb-0">Data akan masuk ke Soft Delete (Terarsip) dan bisa dipulihkan nanti.</p>
            </div>
        </div>

        <x-slot:footer>
            <button type="button" wire:click="delete" class="btn btn-danger px-4">
                <span wire:loading.remove wire:target="delete">Hapus Sekarang</span>
                <span wire:loading wire:target="delete">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                </span>
            </button>
        </x-slot:footer>
    </x-confirmation-modal>
</div>

<script>
    function getModal() {
        const el = document.getElementById('deleteUserModal');
        if (!el) return null;
    
        return bootstrap.Modal.getOrCreateInstance(el);
    }
    
    window.addEventListener('show-delete-modal', () => {
        const modal = getModal();
        modal?.show();
    });
    
    window.addEventListener('hide-delete-modal', () => {
        const modal = getModal();
        modal?.hide();
    });
</script>