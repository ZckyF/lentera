<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h5 class="card-title mb-3 text-center fw-semibold" style="color: #1A237E;">
            Aktivasi Akun Pertama Kali
        </h5>

        <p class="text-muted small text-center mb-4">
            Atur kata sandi baru Anda untuk mengaktifkan akun LENTERA.
        </p>

        <form wire:submit.prevent="activate">
            <div class="mb-3">
                <label class="form-label">Identitas (NIM / NIP)</label>
                <input
                    type="text"
                    class="form-control bg-light"
                    value="{{ $identifier }}"
                    disabled
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Sandi Baru</label>
                <input
                    type="password"
                    wire:model="form.password"
                    class="form-control @error('form.password') is-invalid @enderror"
                    placeholder="Minimal 8 karakter"
                    wire:loading.attr="disabled"
                >
                @error('form.password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Konfirmasi Kata Sandi</label>
                <input
                    type="password"
                    wire:model="form.password_confirmation"
                    class="form-control"
                    placeholder="Ulangi kata sandi baru"
                    wire:loading.attr="disabled"
                >
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background-color: #1A237E; border: none;" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="activate">
                    Aktifkan & Lanjutkan
                </span>
            
                <span wire:loading wire:target="activate">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Memproses...</span>
                    </div>
                </span>
            </button>
        </form>
    </div>
</div>