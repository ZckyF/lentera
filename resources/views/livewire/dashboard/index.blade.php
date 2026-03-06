<div class="row g-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
            <div>
                <h4 class="mb-1 fw-semibold">Dashboard Admin</h4>
                <div class="text-muted small">Selamat datang, {{ auth()->user()->name }}.</div>
            </div>
            <div class="text-muted small">
                <i class="bi bi-shield-fill-check me-1 text-success"></i>Akun aktif
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Pengguna</div>
                        <div class="fs-4 fw-semibold">—</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white bg-primary" style="width: 42px; height: 42px;">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Dokumen</div>
                        <div class="fs-4 fw-semibold">—</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white bg-primary" style="width: 42px; height: 42px;">
                        <i class="bi bi-file-earmark-text fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-semibold">Aktivitas</div>
                    <span class="badge text-bg-primary">Beta</span>
                </div>
                <div class="text-muted small">
                    Halaman ini adalah kerangka dashboard admin. Selanjutnya kita bisa isi metrik pengguna/dokumen, log aktivitas, dan quick actions.
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Quick Actions</div>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users') }}" wire:navigate class="btn btn-primary">
                        <i class="bi bi-people me-2"></i>Kelola Pengguna
                    </a>
                    <a href="{{ route('admin.documents') }}" wire:navigate class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-text me-2"></i>Lihat Dokumen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

