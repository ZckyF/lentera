<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentChatSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory()->count(5)->create(['status' => 'active']);

        foreach ($users as $user) {
            $documents = Document::factory()->count(2)->create([
                'uploaded_by' => $user->id,
                'page_count' => fake()->numberBetween(10, 100)
            ]);

            foreach ($documents as $doc) {
                for ($i = 1; $i <= 5; $i++) {
                    DocumentChunk::create([
                        'document_id' => $doc->id,
                        'content' => "Ini adalah potongan teks akademik ke-$i untuk materi " . $doc->title . ". Membahas detail teknis pada halaman " . ($i * 2),
                        'chunk_order' => $i,
                        'page_number' => $i * 2,
                    ]);
                }
            }

            for ($s = 0; $s < 3; $s++) {
                $session = ChatSession::factory()->create([
                    'user_id' => $user->id,
                    'title' => fake()->sentence(3),
                ]);

                $randomDocId = $documents->random()->id;

                $conversation = [
                    ['user', 'Bisa jelaskan materi bab 3 tentang struktur data?'],
                    ['ai', 'Tentu. Bab 3 membahas array, linked list, dan stack. Mau kita bahas dari mana dulu?'],
                    ['user', 'Dari linked list saja. Apa bedanya dengan array?'],
                    ['ai', 'Array alokasi memorinya kontigu dan ukuran tetap. Linked list terdiri dari node yang berpointer ke node berikutnya.'],
                    ['user', 'Terima kasih, jadi untuk tugas saya lebih cocok pakai linked list.'],
                    ['ai', 'Betul, kalau jumlah elemen berubah-ubah linked list lebih fleksibel.'],
                ];

                foreach ($conversation as $idx => [$sender, $message]) {
                    ChatMessage::create([
                        'session_id' => $session->id,
                        'sender' => $sender,
                        'message' => $message,
                        'doc_reference' => $idx === 1 ? json_encode(['document_id' => $randomDocId, 'page' => 3]) : null,
                    ]);
                }
            }
        }
    }
}
