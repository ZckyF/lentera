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
            overflow: hidden;
            position: relative;
        }
        .chat-message-list {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 120px;
        }
        .chat-bubble {
            max-width: min(80%, 760px);
            white-space: pre-wrap;
            word-break: break-word;
+        }
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
                <button type="button" class="btn btn-primary w-100 mb-3" wire:click="newChat">
                    <i class="bi bi-plus-lg me-2"></i>New Chat
                </button>

                <div class="overflow-auto pe-1">
                    @foreach($this->groupedConversations as $group => $items)
                        <div class="small text-uppercase text-muted fw-semibold mb-2 mt-3">{{ $group }}</div>
                        <div class="d-flex flex-column gap-1">
                            @foreach($items as $item)
                                <div
                                    wire:click="openConversation('{{ $item['id'] }}')"
                                    class="chat-history-item p-2 d-flex align-items-start justify-content-between {{ $activeConversationId === $item['id'] ? 'active' : '' }}"
                                >
                                    <span class="small text-truncate pe-2">{{ $item['title'] }}</span>
                                    <i class="bi bi-three-dots text-muted"></i>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
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
                                        <a class="dropdown-item" href="{{ route('settings.profile') }}" wire:navigate>
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

                <div class="chat-message-list px-3 px-md-4 py-4" x-ref="chatScroll">
                    @forelse($messages as $message)
                        @if($message['role'] === 'user')
                            <div class="d-flex justify-content-end mb-3">
                                <div class="chat-bubble bg-primary text-white rounded-4 px-3 py-2 shadow-sm">
                                    {{ $message['content'] }}
                                </div>
                            </div>
                        @else
                            <div class="d-flex justify-content-start mb-3">
                                <div class="chat-bubble d-flex gap-2">
                                    <div class="pt-1">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-circle p-2">
                                            <i class="bi bi-robot"></i>
                                        </span>
                                    </div>
                                    <div class="rounded-4 px-3 py-2 bg-body-tertiary border">
                                        {{ $message['content'] }}
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
                                x-on:input="autoResize($el)"
                                x-on:keydown.enter.prevent="$event.shiftKey || $wire.sendMessage()"
                                class="form-control"
                                rows="1"
                                placeholder="Ask Lentera AI..."
                            ></textarea>
                            <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                        @error('prompt') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        <div class="text-center text-muted small mt-2">
                            Lentera AI can make mistakes. Check important info.
                        </div>
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
            <button type="button" class="btn btn-primary w-100 mb-3" wire:click="newChat" data-bs-dismiss="offcanvas">
                <i class="bi bi-plus-lg me-2"></i>New Chat
            </button>

            @foreach($this->groupedConversations as $group => $items)
                <div class="small text-uppercase text-muted fw-semibold mb-2 mt-3">{{ $group }}</div>
                <div class="d-flex flex-column gap-1">
                    @foreach($items as $item)
                        <div
                            wire:click="openConversation('{{ $item['id'] }}')"
                            class="chat-history-item p-2 d-flex align-items-start justify-content-between {{ $activeConversationId === $item['id'] ? 'active' : '' }}"
                            data-bs-dismiss="offcanvas"
                        >
                            <span class="small text-truncate pe-2">{{ $item['title'] }}</span>
                            <i class="bi bi-three-dots text-muted"></i>
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

            const scrollToBottom = () => {
                if (chatScroll) chatScroll.scrollTop = chatScroll.scrollHeight;
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

            scrollToBottom();
        });
    </script>
</div>


