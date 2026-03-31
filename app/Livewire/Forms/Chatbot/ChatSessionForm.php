<?php

namespace App\Livewire\Forms\Chatbot;

use Livewire\Form;
use App\Models\ChatSession;

class ChatSessionForm extends Form
{
    public $id;
    public string $title;

    public function rules()
    {
        return [
            'title' => 'required|min:3|max:50',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul harus diisi.',
            'title.min' => 'Judul minimal 3 karakter.',
            'title.max' => 'Judul maksimal 50 karakter.',
        ];
    }

    public function update()
    {
        $this->validate();
        ChatSession::find($this->id)->update(['title' => $this->title]);
    }
}