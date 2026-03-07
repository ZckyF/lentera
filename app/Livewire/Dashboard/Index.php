<?php

namespace App\Livewire\Dashboard;

use App\Models\Document;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Index extends Component
{
    public function render()
    {
        $days = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));
        
        $interactionData = $days->map(function($day) {
            return \App\Models\ChatMessage::whereDate('created_at', $day)->count();
        });
        $docDistribution = \App\Models\Document::selectRaw('year, count(*) as total')
            ->groupBy('year')
            ->get();
        
        return view('livewire.dashboard.index',[
            'totalUsers' => User::count(),
            'totalDocuments' => Document::count(),
            'days' => $days->map(fn($d) => date('d M', strtotime($d))),
            'interactionData' => $interactionData,
            'docLabels' => $docDistribution->pluck('year'),
            'docValues' => $docDistribution->pluck('total'),
        ]);
    }
}

