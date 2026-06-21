<?php

use App\Models\Evento;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public string $search = '';
    public string $date = '';

    #[Computed]
    public function eventos()
    {
        return Evento::query()
            ->where('data_evento', '>=', now())
            ->when($this->search, function ($query) {
                $query->where('titulo', 'like', '%' . $this->search . '%');
            })
            ->when($this->date, function ($query) {
                $query->whereDate('data_evento', $this->date);
            })
            ->orderByRaw('vagas_disponiveis > 0 DESC')
            ->orderBy('data_evento')
            ->get();
    }
};
?>

<div>
    <div class="text-center max-w-3xl mx-auto mb-12 lg:mb-16">
        <h1 class="text-3xl sm:text-4xl lg:text-6xl font-black tracking-tight text-zinc-900 dark:text-white mb-6 px-2">
            Descubra o seu próximo <span class="text-blue-600">evento</span> TI
        </h1>

        <div class="p-2 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row gap-2 max-w-2xl mx-auto">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Pesquisar por título..."
                class="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 text-sm px-4 text-zinc-900 dark:text-white"
            >

            <div class="flex items-center px-4 border-t sm:border-t-0 sm:border-l border-zinc-100 dark:border-zinc-800">
                <input
                    type="date"
                    wire:model.live="date"
                    class="bg-transparent border-none focus:outline-none focus:ring-0 text-xs sm:text-sm text-zinc-900 dark:text-white cursor-pointer w-full sm:w-auto"
                >
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        @forelse ($this->eventos as $evento)
            <div class="group bg-white dark:bg-zinc-900 rounded-3xl shadow-md overflow-hidden border border-zinc-100 dark:border-zinc-800 flex flex-col justify-between">
                
                {{-- BANNER COM OVERLAY DE IMAGEM --}}
                <div class="relative w-full h-48 overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                    <img src="{{ !empty($evento->foto) ? $evento->foto : 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=600' }}" 
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                         alt="Banner do Evento">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    
                    <div class="absolute top-4 left-4 z-10">
                        <span class="{{ $evento->vagas_disponiveis > 0 ? 'bg-blue-600' : 'bg-red-600' }} text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-lg uppercase">
                            {{ $evento->vagas_disponiveis }} Vagas
                        </span>
                    </div>
                </div>

                {{-- CONTEÚDO DO CARD --}}
                <div class="p-6 flex flex-col flex-grow gap-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                            {{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y') }}
                        </span>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-2 mb-2 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ $evento->titulo }}
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                            {{ $evento->descricao ?? 'Sem descrição disponível.' }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-zinc-100 dark:border-zinc-800 mt-auto">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            Restam {{ $evento->vagas_disponiveis }} vagas
                        </span>
                        <a href="{{ route('eventos.show', $evento) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition shadow-md shadow-blue-500/10">
                            Ver Detalhes
                        </a>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full py-20 text-center text-zinc-500 dark:text-zinc-400">
                Nenhum evento encontrado para os termos pesquisados.
            </div>
        @endforelse
    </div>
</div>