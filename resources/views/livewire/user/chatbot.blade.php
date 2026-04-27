<div
    class="lentera-chat-shell"
>
    <style>
        .lentera-chat-shell {
            height: 100vh;
            width: 100vw;
        }
        .lentera-chat-card {
            height: 100%;
            width: 100%;
            border-radius: 0;
            border: none;
        }
        .chat-sidebar {
            width: 280px;
            flex-shrink: 0;
            height: 100%;
        }
        .chat-history-item {
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .chat-history-item:hover {
            background: var(--bs-secondary-bg);
        }
        .chat-history-item.active {
            background: var(--bs-primary-bg-subtle);
            border: 1px solid var(--bs-primary-border-subtle);
        }
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            position: relative;
        }
        .chat-bubble {
            max-width: 80%;
            white-space: pre-wrap;
            /* word-break: break-word; */
            padding: 0rem 1rem;
            margin-bottom: 30px;
        }

        .user-bubble {
            border-radius: 1.25rem 1.25rem 0.25rem 1.25rem;
        }

        .ai-container {
            max-width: 900px;
        }

        .ai-content {
            font-size: 1.05rem;
            color: var(--bs-body-color);
        }

        .ai-content ul {
            list-style: disc !important;
            padding-left: 1.5rem !important;
        }

        .ai-content ol {
            list-style: decimal !important;
            padding-left: 1.5rem !important;
        }

        /* Tambahkan ini di file CSS kamu */
        .ai-content table {
            width: 100%;
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }

        .ai-content th, .ai-content td {
            padding: 0.75rem;
            border: 1px solid #dee2e6; /* Ini yang memunculkan garis kotak */
            text-align: center;
        }

        .ai-content thead {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .chat-message-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            margin-bottom: 100px;
            flex-direction: column;
            scroll-behavior: smooth;
            scrollbar-width: thin;
        }
        
        .chat-input-wrap {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, var(--bs-body-bg) 30%);
        }
        .chat-input-pill {
            border-radius: 1rem;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
        }
        .chat-input-pill textarea {
            resize: none;
            max-height: 200px;
            overflow-y: auto;
            border: 0;
            box-shadow: none;
            background: transparent;
        }
        @media (max-width: 991.98px) {
            .lentera-chat-shell {
                height: calc(100vh - 1rem);
            }
            .chat-bubble {
                max-width: 92%;
            }
        }
    </style>

    <div class="card border-0 shadow-sm lentera-chat-card position-relative">
        <div class="d-flex h-100">
            <aside class="chat-sidebar d-none d-lg-flex flex-column p-3">
            <a href="{{ route('chatbot') }}" wire:navigate class="btn btn-primary w-100 mb-3">
                <i class="bi bi-plus-lg"></i> Chat Baru
            </a>

                <div class="overflow-auto pe-1 flex-grow-1">
                    @if(!empty($this->groupedConversations))
                        @foreach($this->groupedConversations as $group => $items)
                            <div class="small text-uppercase text-muted fw-semibold mb-2 mt-4 px-2">{{ $group }}</div>
                            <div class="d-flex flex-column gap-1">
                                @foreach($items as $item)
                                    <div class="chat-history-item d-flex align-items-center position-relative rounded-2 {{ $activeConversationId === $item['id'] ? 'active bg-primary-subtle' : '' }}">
                                        <a wire:key="session-{{ $item['id'] }}"
                                        href="{{ route('chatbot', $item['slug']) }}"
                                        wire:navigate
                                        data-bs-dismiss="offcanvas"
                                        class="flex-grow-1 p-2 text-decoration-none text-truncate d-flex align-items-center overflow-hidden">
                                            <span class="small text-body {{ $activeConversationId === $item['id'] ? 'fw-semibold' : '' }}">
                                                {{ $item['title'] }}
                                            </span>
                                        </a>

                                        <div class="dropdown pe-2">
                                            <button class="btn btn-link btn-sm p-0 text-muted border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <button class="dropdown-item small" 
                                                            wire:click="setEditSession({{ $item['id'] }}, '{{ $item['title'] }}')" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editTitleModal">
                                                        <i class="bi bi-pencil me-2"></i> Edit Judul
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item small text-danger" 
                                                            wire:click="setDeleteSession('{{ $item['id'] }}')" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteConfirmModal">
                                                        <i class="bi bi-trash me-2"></i> Hapus
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-chat-dots text-muted opacity-50" style="font-size: 2rem;"></i>
                            </div>
                            <p class="small text-muted mb-0">Belum ada riwayat chat.</p>
                            <p class="x-small text-muted opacity-75">Mulai obrolan baru sekarang!</p>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="chat-main flex-grow-1 position-relative">
                <nav class="navbar bg-body border-bottom sticky-top z-2">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#chatSidebarOffcanvas" aria-controls="chatSidebarOffcanvas">
                                <i class="bi bi-list"></i>
                            </button>
                        </div>

                        <span class="navbar-brand mx-auto mb-0 h6 text-truncate" style="max-width: 60%;">
                            {{ $chatTitle ?: 'Lentera AI' }}
                        </span>

                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary shadow-sm rounded-pill px-3" id="themeToggle" onclick="toggleTheme()">
                                <i id="themeIcon" class="bi bi-sun"></i>
                            </button>

                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i>
                                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('settings.profile') }}" wire:navigate data-bs-dismiss="offcanvas">
                                            <i class="bi bi-person me-2"></i>Profile
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                            <i class="bi bi-box-arrow-right"></i>
                                            <span>Keluar</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="chat-message-list px-3 px-md-4 py-4" id="chatScroll">
                    @forelse($messages as $message)
                        @if($message['role'] === 'user')
                            <div class="d-flex justify-content-end mb-2">
                                <div class="chat-bubble user-bubble bg-primary text-white shadow-sm">
                                    {{ $message['content'] }}
                                </div>
                            </div>
                            @else
                                <div class="d-flex justify-content-start mb-5">
                                    <div class="ai-container d-flex gap-3 w-100">
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-circle p-2">
                                                <i class="bi bi-robot"></i>
                                            </span>
                                        </div>
                                        <div class="ai-content flex-grow-1">
                                            <div class="text-body lh-lg">
                                                <x-markdown>
                                                    {{-- Kita ganti \n menjadi dua kali enter agar tabelnya terpisah dari paragraf --}}
                                                    {!! nl2br(e($message['content'])) !!}
                                                </x-markdown>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                    @empty
                        <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                            Belum ada pesan. Mulai percakapan baru.
                        </div>
                    @endforelse
                </div>

                <div class="chat-input-wrap px-3 px-md-4 pb-3">
                    <form wire:submit="sendMessage" class="mx-auto" style="max-width: 900px;">
                        <div class="chat-input-pill p-2 d-flex align-items-end gap-2 shadow-sm">
                            <textarea
                                wire:model="prompt"
                                id="chatInput"
                                oninput="this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px';"
                                wire:keydown.enter.prevent="sendMessage"
                                class="form-control"
                                rows="1"
                                placeholder="Bertanya ke lentera AI..."
                            ></textarea>
                            <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;" wire:loading.attr="disabled">
                                <span wire:loading wire:target="sendMessage" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            
                                <span wire:loading.remove wire:target="sendMessage"><i class="bi bi-send-fill"></i></span>
                            </button>
                        </div>
                        @error('prompt') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="chatSidebarOffcanvas" aria-labelledby="chatSidebarOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="chatSidebarOffcanvasLabel">Chat History</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <a href="{{ route('chatbot') }}" wire:navigate class="btn btn-outline-primary w-100 mb-3">
                <i class="bi bi-plus-lg"></i> Chat Baru
            </a>

            @foreach($this->groupedConversations as $group => $items)
                <div class="small text-uppercase text-muted fw-semibold mb-2 mt-3 px-2">{{ $group }}</div>
                <div class="d-flex flex-column gap-1">
                @foreach($items as $item)
                    <div class="chat-history-item d-flex align-items-center rounded-2 mb-1 {{ $activeConversationId === $item['id'] ? 'active bg-primary-subtle' : '' }}">
                        
                        <a 
                            href="{{ route('chatbot', $item['slug']) }}" 
                            wire:navigate
                            wire:key="mobile-link-{{ $item['id'] }}"
                            data-bs-dismiss="offcanvas"
                            class="flex-grow-1 p-2 text-decoration-none text-truncate d-flex align-items-center overflow-hidden"
                        >
                            <span class="small text-body {{ $activeConversationId === $item['id'] ? 'fw-semibold' : '' }}">
                                {{ $item['title'] }}
                            </span>
                        </a>

                        <div class="dropdown pe-2">
                            <button 
                                class="btn btn-link btn-sm p-0 text-muted border-0" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                            >
                                <i class="bi bi-three-dots"></i>
                            </button>
                            
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <button class="dropdown-item small" 
                                            wire:click="setEditSession({{ $item['id'] }}, '{{ $item['title'] }}')" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editTitleModal">
                                        <i class="bi bi-pencil me-2"></i> Edit Judul
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item small text-danger" 
                                            wire:click="setDeleteSession('{{ $item['id'] }}')" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteConfirmModal">
                                        <i class="bi bi-trash me-2"></i> Hapus
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endforeach
                </div>
            @endforeach
        </div>
    </div>
    
    <x-confirmation-modal id="logoutModal" title="Keluar dari LENTERA" type="danger">
        Apakah Anda yakin ingin keluar saat ini ?
        <x-slot:footer>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger px-4">Keluar</button>
            </form>
        </x-slot:footer>
    </x-confirmation-modal>

    <x-confirmation-modal id="editTitleModal" title="Ubah Judul Percakapan" type="primary">
        <div class="mb-0">
            <label class="form-label small fw-bold">Judul Baru</label>
            <input type="text" 
                class="form-control" 
                wire:model="editingTitle" 
                placeholder="Masukkan judul baru...">
            @error('sessionForm.title') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        
    <x-slot:footer>
        <button type="button" 
                class="btn btn-primary px-4 d-flex align-items-center" 
                wire:click="updateSessionTitle"
                wire:loading.attr="disabled">
            
            <span wire:loading wire:target="updateSessionTitle" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            
            <span wire:loading.remove wire:target="updateSessionTitle">Simpan Perubahan</span>
        </button>
    </x-slot:footer>
    </x-confirmation-modal>

    <x-confirmation-modal id="deleteConfirmModal" title="Hapus Chat" type="danger">
        Apakah Anda yakin ingin menghapus riwayat percakapan ini? Data yang dihapus tidak dapat dikembalikan.
        
        <x-slot:footer>
            <button type="button" 
                    class="btn btn-danger px-4" 
                    wire:click="deleteSession">
            <span wire:loading wire:target="deleteSession" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>

            <span wire:loading.remove wire:target="deleteSession">Ya,Hapus</span>
            </button>
        </x-slot:footer>
    </x-confirmation-modal>

    <x-toast />

    @livewireScripts

    <script>
        function applyTheme() {
            const savedTheme = localStorage.getItem('lentera_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            
            document.addEventListener('DOMContentLoaded', () => {
                updateIcon(savedTheme);
            });
        }
    
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('lentera_theme', newTheme);
            updateIcon(newTheme);
        }
    
        function updateIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
            }
        }
    
        applyTheme();

        document.addEventListener('DOMContentLoaded', () => {
            const chatScroll = document.querySelector('.chat-message-list');
            const chatInput = document.querySelector('textarea');
            const container = document.getElementById('chatScroll');

            const scrollToBottom = () => {
                if (chatScroll) chatScroll.scrollTop = chatScroll.scrollHeight;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            };

            if (chatInput) {
                chatInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }

            if (window.Livewire) {
                Livewire.on('chat-message-added', () => {
                    setTimeout(scrollToBottom, 50);
                });
            }

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('chat-message-added', () => {
                    setTimeout(scrollToBottom, 50);
                });
            });

            scrollToBottom();
        });

        document.addEventListener('close-modal', event => {
            let modalElement = document.getElementById(event.detail.id);
            let modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    </script>
</div>


