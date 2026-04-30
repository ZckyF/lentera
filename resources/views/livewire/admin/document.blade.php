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
                        <i class="bi {{ $showHistory ? 'bi-archive' : 'bi-file-earmark-text' }} me-2 text-primary"></i>
                        {{ $showHistory ? 'Arsip Dokumen' : 'Dokumen Aktif' }}
                    </h5>
                    <div class="text-muted small">
                        {{ $showHistory ? 'Daftar dokumen yang telah dihapus sementara.' : 'Manajemen dokumen Lentera.' }}
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
                    @if($isAdding)
                        Tambah Dokumen
                    @elseif($isViewing)
                        Detail Dokumen (Arsip)
                    @else
                        Edit Dokumen
                    @endif
                </h6>

                @if($isAdding)
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center p-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>
                            <div class="fw-bold text-uppercase" style="font-size: 0.8rem;">Penting: Struktur Dokumen Legal</div>
                            <span class="small">
                                Pastikan PDF memiliki format struktur yang jelas (**BAB, Pasal, dan Ayat**). 
                                Lentera AI butuh struktur ini untuk proses <i>chunking</i> agar mahasiswa bisa tanya pasal spesifik dengan akurat. Untuk contoh struktur PDF, silahkan lihat dokumen peraturan akademik polnes 2024.
                            </span>
                        </div>
                    </div>
                @endif

                <form wire:submit="{{ $isAdding ? 'store' : 'update' }}">
                    <div class="row g-3">
                        <div class="{{ $isAdding ? 'col-md-4' : 'col-md-5' }}">
                            <label class="form-label small fw-bold text-muted">Judul Dokumen</label>
                            <input type="text" wire:model="form.title" class="form-control @error('form.title') is-invalid @enderror" @disabled($isViewing)>
                            @error('form.title') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                        </div>

                        <div class="{{ $isAdding ? 'col-md-2' : 'col-md-4' }}">
                            <label class="form-label small fw-bold text-muted">Tahun</label>
                            <input type="number" wire:model="form.year" class="form-control @error('form.year') is-invalid @enderror" min="1900" max="2099" @disabled($isViewing)>
                            @error('form.year') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                        </div>
                        @if(!$isAdding)
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Status</label>
                            <select wire:model="form.status" class="form-select @error('form.status') is-invalid @enderror" @disabled($isViewing)>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                                @error('form.status') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror
                            </div>
                        @endif
                        
                        @if($isAdding)
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">File Dokumen</label>

                                @if($isViewing && isset($form->document) && $form->document?->file_path)
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($form->document->file_path) }}" target="_blank" class="btn btn-outline-info">
                                            <i class="bi bi-box-arrow-up-right me-2"></i>Open File
                                        </a>
                                        <span class="text-muted small">{{ basename($form->document->file_path) }}</span>
                                    </div>
                                @else
                                    <div
                                        x-data="{ isUploading: false, progress: 0 }"
                                        x-on:livewire-upload-start="isUploading = true"
                                        x-on:livewire-upload-finish="isUploading = false; progress = 100"
                                        x-on:livewire-upload-error="isUploading = false"
                                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                                    >
                                        <input type="file" wire:model="form.file" class="form-control @error('form.file') is-invalid @enderror" accept=".pdf">
                                        @error('form.file') <div class="invalid-feedback text-xs">{{ $message }}</div> @enderror

                                        <div class="progress mt-2" x-show="isUploading" style="height: 7px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" x-bind:style="`width: ${progress}%`"></div>
                                        </div>
                                        <div class="small text-muted mt-1" x-show="isUploading">
                                            Uploading... <span x-text="progress"></span>%
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

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
                        <th class="ps-4 border-0 py-3 small fw-bold text-muted">JUDUL</th>
                        <th class="border-0 py-3 small fw-bold text-muted">TAHUN</th>
                        <th class="border-0 py-3 small fw-bold text-muted">TIPE FILE</th>
                        <th class="border-0 py-3 small fw-bold text-muted">UKURAN</th>
                        <th class="border-0 py-3 small fw-bold text-muted">STATUS</th>
                        <th class="border-0 py-3 small fw-bold text-muted text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        @php
                            $mime = strtolower((string) $document->mime_type);
                            $extension = strtolower(pathinfo((string) $document->file_path, PATHINFO_EXTENSION));
                            $isPdf = str_contains($mime, 'pdf') || $extension === 'pdf';
                            $iconClass = $isPdf ? 'bi-file-earmark-pdf text-danger' : ($isWord ? 'bi-file-earmark-word text-primary' : 'bi-file-earmark');
                        @endphp
                        <tr wire:key="{{ $document->id }}">
                            <td class="ps-4 fw-medium text-muted">
                                {{ $documents->firstItem() + $loop->index }}
                            </td>
                            <td class="ps-4 fw-medium">{{ $document->title }}</td>
                            <td>{{ $document->year }}</td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-2">
                                    <i class="bi {{ $iconClass }}"></i>
                                    <span class="small text-muted">{{ strtoupper($extension ?: 'FILE') }}</span>
                                </span>
                            </td>
                            <td>{{ number_format(((int) $document->file_size) / 1024 / 1024, 2) }} MB</td>
                            <td>
                                @php
                                    $statusBadge = [
                                        'active' => 'bg-success',
                                        'inactive' => '.bg-danger-subtle',
                                        'failed' => 'bg-danger',
                                        'processing' => 'bg-warning'
                                    ][$document->status];
                                @endphp
                                <span class="badge {{ $statusBadge }} rounded-pill" style="font-size: 0.7rem;">
                                    {{ ucfirst($document->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($showHistory)
                                    <button wire:click="view({{ $document->id }})" class="btn btn-sm btn-outline-info border-0" title="Detail">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>

                                    <button wire:click="download({{ $document->id }})" class="btn btn-sm btn-outline-secondary border-0" title="Download">
                                        <i class="bi bi-download"></i>
                                    </button>

                                    <button wire:click="confirmRestore({{ $document->id }})" class="btn btn-sm btn-outline-success border-0" title="Pulihkan">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                @else
                                    <button wire:click="edit({{ $document->id }})" class="btn btn-sm btn-outline-primary border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <button wire:click="download({{ $document->id }})" class="btn btn-sm btn-outline-secondary border-0" title="Download">
                                        <i class="bi bi-download"></i>
                                    </button>

                                    <button wire:click="confirmDelete({{ $document->id }})" class="btn btn-sm btn-outline-danger border-0">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small italic">Dokumen tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top-0 py-3">
            {{ $documents->links() }}
        </div>
    </div>

    <x-confirmation-modal id="deleteDocumentModal" title="Konfirmasi Hapus" type="danger">
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

    <x-confirmation-modal id="restoreDocumentModal" title="Konfirmasi Pemulihan" type="success">
        <div class="d-flex align-items-center">
            <i class="bi bi-arrow-counterclockwise text-success fs-1 me-3"></i>
            <div>
                <p class="mb-1 fw-bold">Pulihkan dokumen ini?</p>
                <p class="text-muted small mb-0">Dokumen ini akan aktif kembali dan dapat diakses seperti biasa.</p>
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

    window.addEventListener('show-delete-modal', () => {
        getModal('deleteDocumentModal')?.show();
    });

    window.addEventListener('hide-delete-modal', () => {
        getModal('deleteDocumentModal')?.hide();
    });

    window.addEventListener('show-restore-modal', () => {
        getModal('restoreDocumentModal')?.show();
    });

    window.addEventListener('hide-restore-modal', () => {
        getModal('restoreDocumentModal')?.hide();
    });
</script>

