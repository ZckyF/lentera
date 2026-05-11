<?php

namespace App\Livewire\User;

use App\Events\ChatMessageAdded;
use App\Livewire\Forms\Chatbot\ChatSessionForm;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\OpenRouterService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Pgvector\Laravel\Vector;

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
        ],[
            'prompt.required' => 'Prompt wajib diisi.',
            'prompt.max' => 'Prompt maksimal 4000 karakter.',
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

        $queryVector = $aiService->embed($userMessage);

        if ($queryVector) {
            $vectorChunks = DocumentChunk::with('document')
                ->whereHas('document', function($q) {
                    $q->where('status', 'active')->whereNull('deleted_at');
                })
                ->orderByRaw('embedding <=> ?', [new Vector($queryVector)])
                ->limit(15)
                ->get();

                if (preg_match('/Pasal\s+(\d+)/i', $userMessage, $matches)) {
                        $keyword = $matches[0];
                        $keywordChunks = DocumentChunk::with('document')
                            ->whereHas('document', function($q) {
                                $q->where('status', 'active')->whereNull('deleted_at');
                            })
                            ->where('content', 'ILIKE', "%{$keyword}%")
                            ->limit(5)
                            ->get();
                            
                        $relatedChunks = $vectorChunks->merge($keywordChunks)->unique('id');
                    } else {
                        $relatedChunks = $vectorChunks;
                    }

            $processedChunkIds = [];

            foreach ($relatedChunks as $chunk) {
                // Skip kalau chunk ini sudah terbawa sebagai "tetangga" dari chunk sebelumnya
                if (in_array($chunk->id, $processedChunkIds)) continue;

                // Ambil potongan sebelum dan sesudah dari DB
                $neighbors = DocumentChunk::where('document_id', $chunk->document_id)
                    ->whereIn('id', [$chunk->id - 1, $chunk->id + 1])
                    ->orderBy('id', 'asc')
                    ->get();

                $pageInfo = $chunk->page_number ? " (Halaman: {$chunk->page_number})" : "";
                $context .= "--- POTONGAN DOKUMEN ---\n";
                $context .= "Judul: {$chunk->document->title}{$pageInfo}\n";
                
                // Gabungkan konten: Tetangga Sebelum + Chunk Utama + Tetangga Sesudah
                $fullContent = "";
                foreach ($neighbors as $neighbor) {
                    if ($neighbor->id < $chunk->id) {
                        $fullContent .= $neighbor->content . "\n";
                        $processedChunkIds[] = $neighbor->id;
                    }
                }

                $fullContent .= $chunk->content . "\n";
                $processedChunkIds[] = $chunk->id;

                foreach ($neighbors as $neighbor) {
                    if ($neighbor->id > $chunk->id) {
                        $fullContent .= $neighbor->content;
                        $processedChunkIds[] = $neighbor->id;
                    }
                }

                $context .= "Konten: " . trim($fullContent) . "\n";
                $context .= "--- AKHIR POTONGAN ---\n\n";
                
                $docIds[] = $chunk->document_id;
            }
        }

        $systemPrompt = "Anda adalah Lentera AI, asisten akademik yang cerdas, objektif, dan kritis. " .
                        "Gaya bicara Anda santai (chill) namun tetap mendalam.\n\n" .
                        "INSTRUKSI UTAMA:\n" .
                        "1. Gunakan data dari DATA DOKUMEN di bawah untuk menjawab pertanyaan user.\n" .
                        "2. Jika jawaban ada di dokumen, WAJIB sebutkan judul dokumennya.\n" .
                        "3. Jika informasi tidak ditemukan di dokumen, katakan: 'Maaf, informasi tersebut tidak tersedia di basis data akademik saya saat ini.'\n" .
                        "4. JANGAN mengarang jawaban (hallucination).\n" .
                        "5. WAJIB menjawab dalam Bahasa Indonesia, meskipun user bertanya dalam bahasa asing.\n" .
                        "6. Jika terdapat tabel dalam dokumen, JANGAN tampilkan dalam bentuk tabel markdown. Ubah menjadi daftar poin (bullet list) yang rapi dan terstruktur.\n" .
                        "7. Jika terdapat rumus atau LaTeX, ubah menjadi penjelasan teks biasa yang mudah dipahami. Jangan tampilkan simbol LaTeX mentah.\n" .
                        "DATA DOKUMEN:\n" . ($context ?: "Tidak ada dokumen relevan yang ditemukan untuk pertanyaan ini.");

        $aiResponse = $aiService->ask($systemPrompt, $userMessage, $this->messages);

        $aiMsgRecord = ChatMessage::create([
            'session_id' => $this->activeConversationId,
            'sender' => 'ai',
            'message' => $aiResponse,
            'doc_reference' => count($docIds) > 0 ? json_encode(array_values(array_unique($docIds))) : null,
        ]);

        $this->messages[] = ['role' => 'assistant', 'content' => $aiResponse];
        
        // broadcast(new ChatMessageAdded($aiMsgRecord));
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
