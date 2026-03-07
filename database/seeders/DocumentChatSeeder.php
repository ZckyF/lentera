<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentChatSeeder extends Seeder
{
    /**
     * Create 5 users; for each: 2 documents (page_count <= 100), 3 chat sessions with 4+ messages each.
     */
    public function run(): void
    {
        $users = User::factory()
            ->count(5)
            ->state(['status' => 'active'])
            ->create();

        foreach ($users as $user) {
            Document::factory()
                ->count(2)
                ->create(['uploaded_by' => $user->id]);

            for ($s = 0; $s < 3; $s++) {
                $session = ChatSession::create([
                    'user_id' => $user->id,
                    'title' => fake()->sentence(3),
                ]);

                $conversation = [
                    ['user', 'Bisa jelaskan materi bab 3 tentang struktur data?'],
                    ['ai', 'Tentu. Bab 3 membahas array, linked list, dan stack. Mau kita bahas dari mana dulu?'],
                    ['user', 'Dari linked list saja. Apa bedanya dengan array?'],
                    ['ai', 'Array alokasi memorinya kontigu dan ukuran tetap. Linked list terdiri dari node yang berpointer ke node berikutnya, sehingga bisa tumbuh dinamis.'],
                    ['user', 'Terima kasih, jadi untuk tugas saya lebih cocok pakai linked list.'],
                    ['ai', 'Betul, kalau jumlah elemen berubah-ubah linked list lebih fleksibel. Kalau ada soal lain bisa tanya lagi.'],
                ];

                foreach ($conversation as $idx => [$sender, $message]) {
                    ChatMessage::create([
                        'session_id' => $session->id,
                        'sender' => $sender,
                        'message' => $message,
                        'doc_reference' => $idx === 1 ? ['document_id' => 1, 'page' => 3] : null,
                    ]);
                }
            }
        }
    }
}
