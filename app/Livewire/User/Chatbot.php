<?php

namespace App\Livewire\User;

use App\Livewire\Forms\Chatbot\ChatSessionForm;
use App\Models\ChatSession;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.user')]
#[Title('Lentera AI')]
class Chatbot extends Component
{
    public string $chatTitle = 'Lentera AI';
    public string $prompt = '';
    public ?string $activeConversationId = 'conv-1';
    public string $editingSessionId;
    public string $editingTitle;

    public ChatSessionForm $sessionForm;

    /**
     * @var array<int, array{id:string,title:string,date:string}>
     */
    public array $conversations = [];

    /**
     * @var array<int, array{id:string,role:string,content:string,created_at:string}>
     */
    public array $messages = [];

    public function mount(): void
    {
        $this->conversations = [
            ['id' => 'conv-1', 'title' => 'Rangkuman Dokumen Akreditasi', 'date' => now()->toDateString()],
            ['id' => 'conv-2', 'title' => 'Analisis Kebutuhan Sistem', 'date' => now()->toDateString()],
            ['id' => 'conv-3', 'title' => 'Draft Laporan Mingguan', 'date' => now()->subDay()->toDateString()],
        ];

        $this->messages = [
            [
                'id' => (string) str()->uuid(),
                'role' => 'ai',
                'content' => 'Halo, saya Lentera AI. Ada yang bisa saya bantu hari ini?',
                'created_at' => now()->toDateTimeString(),
            ]
        ];
    }

    public function newChat(): void
    {
        $id = 'conv-' . str()->random(8);

        array_unshift($this->conversations, [
            'id' => $id,
            'title' => 'Percakapan Baru',
            'date' => now()->toDateString(),
        ]);

        $this->activeConversationId = $id;
        $this->chatTitle = 'Lentera AI';
        $this->messages = [
            [
                'id' => (string) str()->uuid(),
                'role' => 'assistant',
                'content' => 'Chat baru dimulai. Silakan tulis pertanyaan Anda.',
                'created_at' => now()->toDateTimeString(),
            ],
        ];

        $this->dispatch('chat-message-added');
    }

    public function openConversation(string $id): void
    {
        $conversation = collect($this->conversations)->firstWhere('id', $id);
        if (!$conversation) {
            return;
        }

        $this->activeConversationId = $id;
        $this->chatTitle = $conversation['title'];

        if (count($this->messages) === 0) {
            $this->messages[] = [
                'id' => (string) str()->uuid(),
                'role' => 'assistant',
                'content' => 'Percakapan dimuat. Silakan lanjutkan pertanyaan Anda.',
                'created_at' => now()->toDateTimeString(),
            ];
        }

        $this->dispatch('chat-message-added');
    }

    public function sendMessage(): void
    {
        $this->validate([
            'prompt' => ['required', 'string', 'max:4000'],
        ]);

        $input = trim($this->prompt);

        $this->messages[] = [
            'id' => (string) str()->uuid(),
            'role' => 'user',
            'content' => $input,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->messages[] = [
            'id' => (string) str()->uuid(),
            'role' => 'assistant',
            'content' => 'Ini adalah balasan contoh. Integrasikan ke service AI Anda untuk respons real-time streaming.',
            'created_at' => now()->toDateTimeString(),
        ];

        if ($this->chatTitle === 'Lentera AI' || $this->chatTitle === 'Percakapan Baru') {
            $this->chatTitle = str($input)->limit(48)->toString();
            foreach ($this->conversations as $index => $conversation) {
                if ($conversation['id'] === $this->activeConversationId) {
                    $this->conversations[$index]['title'] = $this->chatTitle;
                    break;
                }
            }
        }

        $this->prompt = '';
        $this->dispatch('chat-message-added');
    }

    // public function getGroupedConversationsProperty(): array
    // {
    //     return collect($this->conversations)
    //         ->groupBy(function (array $conversation) {
    //             $date = $conversation['date'];
    //             if ($date === now()->toDateString()) {
    //                 return 'Today';
    //             }
    //             if ($date === now()->subDay()->toDateString()) {
    //                 return 'Yesterday';
    //             }

    //             return \Carbon\Carbon::parse($date)->translatedFormat('d M Y');
    //         })
    //         ->toArray();
    // }

    public function setEditSession($id, $currentTitle)
    {
        $this->editingTitle = $currentTitle;
        $this->editingSessionId = $id;
    }

    public function updateSessionTitle()
    {
        $this->sessionForm->id = $this->editingSessionId;
        $this->sessionForm->title = $this->editingTitle;
        $this->sessionForm->update();
        $this->dispatch('close-modal', id: 'editTitleModal');
        $this->dispatch('show-toast', 
            message: 'Judul berhasil diperbarui!', 
            type: 'success'
        );
    }

    public function setDeleteSession($id)
    {
        $this->editingSessionId = $id;
    }

    public function deleteSession()
    {
        $session = ChatSession::where('user_id', auth()->id())->find($this->editingSessionId);
        if ($session) {
            if ($this->activeConversationId == $this->editingSessionId) {
                $this->activeConversationId = null;
                $this->messages = [];
            }
            
            $session->delete();
            $this->dispatch('close-modal', id: 'deleteConfirmModal');
            $this->dispatch('show-toast', 
                message: 'Percakapan berhasil dihapus!',
                type: 'success'
            );
        }
    }

    public function getGroupedConversationsProperty(): array
    {
        return ChatSession::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->groupBy(function ($session) {
                $date = $session->created_at->toDateString();
    
                if ($date === now()->toDateString()) {
                    return 'Today';
                }
                if ($date === now()->subDay()->toDateString()) {
                    return 'Yesterday';
                }
    
                return $session->created_at->translatedFormat('d M Y');
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.user.chatbot');
    }
}
