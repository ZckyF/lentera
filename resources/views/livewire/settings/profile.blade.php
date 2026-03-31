<div>
    @if (session()->has('message'))
        <div class="alert alert-success d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ session('message') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif

    <form wire:submit="save">
        <div class="row g-4">
            <div class="col-12">
                <h6 class="fw-bold text-uppercase small mb-3">Informasi Identitas</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Identitas (NIM/NIP)</label>
                        <input type="text" 
                               wire:model="form.identifier" 
                               class="form-control @error('form.identifier') is-invalid @enderror" 
                               @readonly(auth()->user()->role !== 'admin')>
                        @if(auth()->user()->role !== 'admin')
                            <div class="form-text text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-info-circle"></i> Hubungi Admin untuk mengubah nomor identitas.
                            </div>
                        @endif
                        @error('form.identifier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" wire:model="form.name" class="form-control @error('form.name') is-invalid @enderror">
                        @error('form.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-25">

            <div class="col-12">
                <h6 class="fw-bold text-uppercase small mb-3">Keamanan Akun</h6>
                <p class="text-muted small mb-3">Kosongkan kolom password jika tidak ingin mengubahnya.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" wire:model="form.password" class="form-control @error('form.password') is-invalid @enderror" placeholder="******">
                        </div>
                        @error('form.password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <input type="password" wire:model="form.password_confirmation" class="form-control" placeholder="******">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-5 text-end">
                <button type="button" onclick="window.location.reload()" class="btn btn-link text-decoration-none text-muted me-3">Reset</button>
                <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                    <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                    <span wire:loading wire:target="save">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>