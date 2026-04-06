<?php

namespace App\Livewire\User;

use App\Events\ChatMessageAdded;
use App\Livewire\Forms\Chatbot\ChatSessionForm;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Document;
use App\Services\OpenRouterService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

use function Livewire\str;

#[Layout('layouts.user')]
#[Title('Lentera AI')]
class Chatbot extends Component
{
    public string $chatTitle = 'Lentera AI';
    public string $prompt = '';
    public $activeConversationId = null;
    public $editingSessionId;
    public string $editingTitle;

    public ChatSessionForm $sessionForm;

    public array $conversations = [];

    public array $messages = [];

    public function mount($slug = null)
    {
        $this->messages = [
            [
                'role' => 'ai',
                'content' => 'Halo, saya Lentera AI. Ada yang bisa saya bantu hari ini?'
            ]
        ];
        if ($slug) {
            $session = ChatSession::where('user_id', auth()->id())
                ->where('slug', $slug)
                ->first();

            if ($session) {
                $this->activeConversationId = $session->id;
                $this->chatTitle = $session->title;

            $newMessages = $session->chatMessages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'role' => $m->sender === 'user' ? 'user' : 'ai',
                    'content' => $m->message
                ])
                ->toArray();

            $this->messages = [...$this->messages, ...$newMessages];
            } else {
                return redirect()->route('chatbot');
            }
        } else {
            $this->activeConversationId = null;
            $this->chatTitle = 'Lentera AI';
        }
    }

    public function sendMessage(OpenRouterService $aiService): void
    {
        $this->validate([
            'prompt' => ['required', 'string', 'max:4000'],
        ]);

        $userMessage = trim($this->prompt);
        $this->prompt = '';

        if (is_null($this->activeConversationId)) {
            $newSession = ChatSession::create([
                'user_id' => auth()->id(),
                'title' => Str::limit($userMessage, 30),
                'slug' => str()->random(16),
            ]);

            $this->activeConversationId = $newSession->id;
            $this->chatTitle = $newSession->title;
            
            $this->js("window.history.replaceState({}, '', '/chatbot/{$newSession->slug}')");
        }

        ChatMessage::create([
            'session_id' => $this->activeConversationId,
            'sender' => 'user',
            'message' => $userMessage,
        ]);

        $this->messages[] = ['role' => 'user', 'content' => $userMessage];

        $context = "";
        $docIds = [];

        $keywords = ['apa', 'bagaimana', 'siapa', 'kapan', 'jelaskan', 'data', 'dokumen', 'berdasarkan'];
        $isAskingData = Str::contains(Str::lower($userMessage), $keywords);

        if ($isAskingData && strlen($userMessage) > 10) {
            $relatedDocs = Document::where('status', 'active')
                ->where('content_raw', 'LIKE', "%{$userMessage}%")
                ->limit(2) 
                ->get();

            foreach ($relatedDocs as $doc) {
                $context .= "Dokumen: {$doc->title} ({$doc->year})\nIsi: " . Str::limit($doc->content_raw, 1200) . "\n---\n";
                $docIds[] = $doc->id;
            }
        }

        $systemPrompt = "Anda adalah Lentera AI. Jawablah berdasarkan dokumen yang diberikan. 
                WAJIB: Jika jawaban ada di dokumen, sebutkan nomor PASAL atau BAB-nya di akhir kalimat. 
                Contoh: 'Pendaftaran dilakukan di gedung A (Pasal 4)'. 
                Jika tidak ada informasi pasal, sebutkan saja judul dokumennya." . 
            ($context 
                ? "Gunakan data berikut untuk menjawab. Jika tidak ada di data, katakan sejujurnya: \n" . $context 
                : "Jawablah dengan gaya bahasa yang chill namun sopan.");

        $aiResponse = $aiService->ask($systemPrompt, $userMessage);

        $aiMsgRecord = ChatMessage::create([
            'session_id' => $this->activeConversationId,
            'sender' => 'ai',
            'message' => $aiResponse,
            'doc_reference' => count($docIds) > 0 ? json_encode($docIds) : null,
        ]);

        $this->messages[] = ['role' => 'assistant', 'content' => $aiResponse];
        
        broadcast(new ChatMessageAdded($aiMsgRecord))->toOthers();
        
        $this->dispatch('chat-message-added');
    }

    public function getListeners()
    {
        if (! $this->activeConversationId) {
            return [];
        }

        return [
            "echo-private:chat.{$this->activeConversationId},ChatMessageAdded" => 'handleBroadcastedMessage',
        ];
    }

    public function handleBroadcastedMessage($event)
    {
        if ($event['message']['session_id'] == $this->activeConversationId) {
            $this->messages[] = [
                'role' => $event['message']['sender'],
                'content' => $event['message']['message']
            ];
        }
    }

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

        if ($this->editingSessionId === $this->activeConversationId) {
            $this->chatTitle = $this->editingTitle;
        }
        
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

            $this->redirect(route('chatbot'), navigate: true);
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
                    return 'Hari ini';
                }
                if ($date === now()->subDay()->toDateString()) {
                    return 'Kemarin';
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
