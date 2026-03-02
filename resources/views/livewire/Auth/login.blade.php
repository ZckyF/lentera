<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h5 class="card-title mb-3 text-center fw-semibold" style="color: #1A237E;">
            Masuk ke Akun Anda
        </h5>

        <form wire:submit.prevent="login">
            <div class="mb-3">
                <label class="form-label">Identitas (NIM / NIP)</label>
                <input
                    type="text"
                    wire:model.defer="form.identifier"
                    class="form-control @error('form.identifier') is-invalid @enderror"
                    placeholder="Masukkan NIM atau NIP Anda"
                    wire:loading.attr="disabled"
                >
                @error('form.identifier')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Sandi</label>
                <input
                    type="password"
                    wire:model.defer="form.password"
                    class="form-control @error('form.password') is-invalid @enderror"
                    placeholder="Masukkan kata sandi Anda"
                    wire:loading.attr="disabled"
                >
                @error('form.password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background-color: #1A237E; border: none;" wire:loading.attr="disabled">
                <span wire:loading.remove>Masuk</span>       
                <span wire:loading>
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Memproses...</span>
                    </div>
                </span>
            </button>
        </form>

        <hr class="my-4 text-muted">

        <div class="text-center">
            <p class="small text-muted mb-2">
                Belum punya akun atau lupa kata sandi?
            </p>
            <div class="p-3 border rounded bg-light">
                <p class="small mb-2 text-secondary">
                    Silakan hubungi <strong>Admin</strong> dengan melampirkan <strong>Foto KTM</strong> asli Anda.
                </p>
                <a href="https://wa.me/6287777777777" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-2">
                    <i class="bi bi-whatsapp"></i>
                    0877-7777-7777
                </a>
            </div>
        </div>
    </div>
</div>