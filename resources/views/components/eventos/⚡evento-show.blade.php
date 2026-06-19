<?php

use Livewire\Volt\Component;
use App\Models\Evento;

new class extends Component {
    // Definimos a propriedade pública
    public Evento $evento;

    // Forçamos o Volt a mapear o parâmetro da URL da forma correta
    public function mount(Evento $evento)
    {
        $this->evento = $evento;
    }
}; ?>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <div class="mb-4">
                <a href="{{ route('eventos.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar para Eventos</a>
            </div>

            <div class="border-b border-gray-200 pb-4 mb-6">
                <h1 class="text-3xl font-bold text-gray-900">{{ $evento->titulo }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Agendado para: {{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="prose max-w-none text-gray-700 space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Descrição do Evento</h3>
                    <p class="mt-2 text-gray-600">
                        {{ $evento->descricao ?? 'Nenhuma descrição detalhada foi fornecida para este evento.' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="block text-sm font-medium text-gray-500">Localização</span>
                        <span class="text-base font-semibold text-gray-800">{{ $evento->local ?? 'Auditório Principal' }}</span>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="block text-sm font-medium text-gray-500">Vagas Totais</span>
                        <span class="text-base font-semibold text-gray-800">{{ $evento->vagas ?? 'Ilimitadas' }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <a href="{{ route('inscricaos.create', ['evento' => $evento->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    Inscrever-me neste Evento
                </a>
            </div>

        </div>
    </div>
</div>