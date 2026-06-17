<?php

use Livewire\Component;
use App\Models\Inscricao;
use App\Models\Evento;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public Evento $evento;

    public function mount(Evento $evento)
    {
        $this->evento = $evento;
    }

    public function realizarInscricao()
    {
        DB::transaction(function () {

            // Atualiza evento com lock (evita overbooking)
            $evento = Evento::lockForUpdate()->find($this->evento->id);

            // 1. Verificar vagas
            if ($evento->vagas_disponiveis <= 0) {
                session()->flash('erro', 'Sem vagas disponíveis.');
                return;
            }

            // 2. Verificar inscrição duplicada
            $existe = Inscricao::where('participante_id', auth()->id())
                ->where('evento_id', $evento->id)
                ->exists();

            if ($existe) {
                session()->flash('erro', 'Você já está inscrito.');
                return;
            }

            // 3. Criar inscrição
            Inscricao::create([
                'participante_id' => auth()->id(),
                'evento_id' => $evento->id,
                'codigo_qr' => 'QR-' . strtoupper(Str::random(10)),
                'data_inscricao' => now(),
            ]);

            // 4. Atualizar vagas
            $evento->decrement('vagas_disponiveis');

        });

        return redirect()->route('inscricaos.index')
            ->with('success', 'Inscrição realizada com sucesso!');
    }
};
?>

<div class="p-6 w-full max-w-2xl mx-auto">
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Confirmar Inscrição</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Você está prestes a se inscrever no evento: <strong class="text-gray-900 dark:text-white">{{ $evento->titulo }}</strong>
        </p>    
    </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    @if (session('erro'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 font-medium border border-red-200 dark:border-red-800" role="alert">
            {{ session('erro') }}
        </div>
    @endif

    <section class="w-full">
        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 space-y-4">
            <p class="text-gray-700 dark:text-gray-300"><strong>Local:</strong> {{ $evento->local }}</p>
            <p class="text-gray-700 dark:text-gray-300"><strong>Data:</strong> {{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y H:i') }}</p>

            @if($evento->vagas_disponiveis > 0)
                <button wire:click="realizarInscricao" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center">
                    Confirmar Minha Inscrição
                </button>
            @else
                <button disabled class="w-full text-red-500 bg-red-50 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-900/30 dark:text-red-400">
                    Sem vagas disponíveis
                </button>
            @endif
        </div>
    </section>
</div>