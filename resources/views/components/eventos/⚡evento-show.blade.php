<?php

use Livewire\Component;
use App\Models\Evento;

new class extends Component {
    // Definimos a propriedade pública
    public Evento $evento;

    // Forçamos o Volt a mapear o parâmetro do ID da URL
    public function mount(Evento $evento)
    {
        $this->evento = $evento;
    }
    
    // --- ESTA É A CORREÇÃO: Dizemos ao Volt qual o layout mestre da aplicação ---
    public function rendering($view)
    {
        $view->layout('layouts.app'); // Altera para 'components.layouts.app' se o teu layout estiver nessa pasta
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $evento->titulo }}</h1>
            
            <div class="flex items-center text-sm text-gray-500 mb-6 gap-4">
                <span>📍 <strong>Local:</strong> {{ $evento->local }}</span>
                <span>📅 <strong>Data:</strong> {{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y H:i') }}</span>
            </div>

            <p class="text-gray-700 leading-relaxed mb-4">
                {{ $evento->descricao }}
            </p>

            <div class="border-t pt-4 mt-6">
                <span class="text-sm text-gray-600">👥 Vagas disponíveis: <strong>{{ $evento->vagas_disponiveis }}</strong> / {{ $evento->capacidade_maxima }}</span>
            </div>
        </div>
    </div>
</div>