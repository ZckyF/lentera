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

    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Pengguna</div>
                        <div class="fs-4 fw-semibold">{{ number_format($totalUsers) }}</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white bg-primary" style="width: 42px; height: 42px;">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Dokumen</div>
                        <div class="fs-4 fw-semibold">{{ number_format($totalDocuments) }}</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white bg-primary" style="width: 42px; height: 42px;">
                        <i class="bi bi-file-earmark-text fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="fw-semibold mb-3">Tren Tanya Jawab AI</div>
                    <div id="chart-interaction"></div>
                </div>
            </div>
        </div>
    
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="fw-semibold mb-3">Proporsi Dokumen (Tahun)</div>
                    <div id="chart-distribution"></div>
                </div>
            </div>
        </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:navigated', function () {
        
        function initCharts() {
            const elA = document.querySelector("#chart-interaction");
            const elB = document.querySelector("#chart-distribution");

            // Hancurkan instance lama jika ada agar tidak duplikat
            if (window.chartInteraction) window.chartInteraction.destroy();
            if (window.chartDistribution) window.chartDistribution.destroy();

            // Deteksi Tema
            const storedTheme = localStorage.getItem('lentera_theme');
            const isDark = (storedTheme === 'dark') 
                || (!storedTheme && document.documentElement.getAttribute('data-bs-theme') === 'dark');

            const mutedTextColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(148, 163, 184, 0.1)' : 'rgba(0, 0, 0, 0.05)';

            const commonOptions = {
                chart: {
                    fontFamily: 'Inter, system-ui, sans-serif',
                    foreColor: mutedTextColor,
                    toolbar: { show: false },
                    background: 'transparent'
                },
                theme: { mode: isDark ? 'dark' : 'light' },
                grid: { borderColor: gridColor },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };

            if (elA) {
                window.chartInteraction = new ApexCharts(elA, {
                    ...commonOptions,
                    series: [{ name: 'Total Pesan', data: @json($interactionData) }],
                    chart: { ...commonOptions.chart, type: 'area', height: 250 },
                    colors: ['#3b82f6'],
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: { categories: @json($days) }
                });
                window.chartInteraction.render();
            }

            if (elB) {
                window.chartDistribution = new ApexCharts(elB, {
                    ...commonOptions,
                    series: @json($docValues),
                    chart: { ...commonOptions.chart, type: 'donut', height: 280 },
                    labels: @json($docLabels),
                    colors: ['#3b82f6', '#8b5cf6', '#6366f1', '#ec4899'],
                    legend: { position: 'bottom' },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    show: true,
                                    name: { show: true, color: mutedTextColor },
                                    value: { color: isDark ? '#f1f5f9' : '#1e293b' },
                                    total: { show: true, label: 'Total', color: mutedTextColor }
                                }
                            }
                        }
                    }
                });
                window.chartDistribution.render();
            }
        }

        initCharts();
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-bs-theme') {
                    initCharts();
                }
            });
        });

        observer.observe(document.documentElement, { attributes: true });
    });
</script>
@endpush

