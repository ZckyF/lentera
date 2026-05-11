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
                        <i class="bi {{ $showHistory ? 'bi-archive' : 'bi-people' }} me-2 text-primary"></i>
                        {{ $showHistory ? 'Arsip Pengguna' : 'Pengguna Aktif' }}
                    </h5>
                    <div class="text-muted small">
                        {{ $showHistory ? 'Daftar akun yang telah dinonaktifkan/dihapus sementara.' : 'Manajemen data pengguna Lentera.' }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button wire:click="toggleHistory" class="btn {{ $showHistory ? 'btn-dark' : 'btn-outline-secondary' }} px-3 shadow-sm">
                        <i class="bi {{ $showHistory ? 'bi-arrow-left me-2' : 'bi-clock-history me-2' }}"></i>
                        {{ $showHistory ? 'Kembali' : 'Riwayat' }}
                    </button>
                    @if(!$showHistory)
                    <button wire:click="create" class="btn btn-primary px-4 shadow-sm" @disabled($isAdding || $isEditing)>
                        <i class="bi bi-plus-lg me-2"></i>Tambah
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($isAdding || $isEditing || $isViewing)
    <div class="card border-0 shadow-sm mb-4 border-start border-4 {{ $isAdding ? 'border-success' : ($isViewing ? 'border-info' : 'border-primary') }}">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4 text-uppercase small tracking-wider">
                @if($isAdding) Tambah Pengguna @elseif($isViewing) Detail Pengguna (Arsip) @else Edit Pengguna @endif
            </h6>
            
            <form wire:submit="{{ $isAdding ? 'store' : 'update' }}">
                <div class="row g-3">
                    <div class="{{$isAdding ? 'col-md-4' : 'col-md-3'}}">
                        <label class="form-label small fw-bold text-muted">Identifier (NIM/NIP)</label>
                        <input type="text" wire:model="form.identifier" class="form-control @error('form.identifier') is-invalid @enderror" @disabled($isViewing)>
                        @error('form.identifier') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>

                    <div class="{{$isAdding ? 'col-md-4' : 'col-md-3'}}">
                        <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                        <input type="text" wire:model="form.name" class="form-control @error('form.name') is-invalid @enderror" @disabled($isViewing)>
                        @error('form.name') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Role</label>
                        <select wire:model="form.role" class="form-select @error('form.role') is-invalid @enderror" @disabled($isViewing)>
                            <option value="admin">Admin</option>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>

                    @if(!$isAdding)
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Status Akun</label>
                        <select wire:model="form.status" class="form-select @error('form.status') is-invalid @enderror" @disabled($isViewing)>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('form.status') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>
                    @endif

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Password {{ $isEditing ? '(Opsional)' : '' }}</label>
                        <input type="password" wire:model="form.password" class="form-control @error('form.password') is-invalid @enderror" placeholder="min. 6 karakter">
                        @error('form.password') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <button type="button" wire:click="cancel" class="btn btn-light border px-4">
                            {{ $isViewing ? 'Tutup' : 'Batal' }}
                        </button>
                        
                        @if(!$isViewing)
                            <button type="submit" class="btn text-white {{ $isAdding ? 'btn-success' : 'btn-primary' }} px-4">
                                <span wire:loading.remove>Simpan Data</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span></span>
                            </button>
                        @endif
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
                                    'inactive' => 'bg-danger'
                                ][$user->status];
                            @endphp
                            <span class="badge {{ $statusBadge }} rounded-pill" style="font-size: 0.7rem;">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($showHistory)
                                <button wire:click="view({{ $user->id }})" class="btn btn-sm btn-outline-info border-0" title="Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                                
                                <button wire:click="confirmRestore({{ $user->id }})" class="btn btn-sm btn-outline-success border-0" title="Pulihkan">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            @else
                                <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-outline-primary border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                <button wire:click="confirmDelete({{ $user->id }})" class="btn btn-sm btn-outline-danger border-0">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted small italic">Tidak ada data pengguna.</td>
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

    <x-confirmation-modal id="restoreUserModal" title="Konfirmasi Pemulihan" type="success">
        <div class="d-flex align-items-center">
            <i class="bi bi-arrow-counterclockwise text-success fs-1 me-3"></i>
            <div>
                <p class="mb-1 fw-bold">Pulihkan akun ini?</p>
                <p class="text-muted small mb-0">Akun ini akan aktif kembali dan bisa login ke sistem seperti biasa.</p>
            </div>
        </div>
    
        <x-slot:footer>
            <button type="button" wire:click="restore" class="btn btn-success px-4 text-white">
                <span wire:loading.remove wire:target="restore">Ya, Pulihkan</span>
                <span wire:loading wire:target="restore">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                </span>
            </button>
        </x-slot:footer>
    </x-confirmation-modal>
</div>

<script>
    function getModal(id) {
        const el = document.getElementById(id);
        if (!el) return null;
    
        return bootstrap.Modal.getOrCreateInstance(el);
    }
    
    // Listener Delete
    window.addEventListener('show-delete-modal', () => {
        getModal('deleteUserModal')?.show(); // Kirim ID yang benar
    });
    
    window.addEventListener('hide-delete-modal', () => {
        getModal('deleteUserModal')?.hide();
    });

    // Listener Restore
    window.addEventListener('show-restore-modal', () => {
        getModal('restoreUserModal')?.show(); // Kirim ID yang benar
    });

    window.addEventListener('hide-restore-modal', () => {
        getModal('restoreUserModal')?.hide();
    });
</script>