<?php

use Livewire\Component;
use App\Models\Presenca;
use App\Models\Evento;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $evento_id = '';

    public function updatingEventoId()
    {
        $this->resetPage();
    }

    public function rendering($view){
        $view->title('Presenças por Evento');
    }

    public function exportar()
    {
        if (!$this->evento_id) {
            session()->flash('erro', 'Por favor, selecione um evento específico para exportar a lista.');
            return;
        }
        return redirect()->route('presencas.imprimir', ['evento' => $this->evento_id]);
    }

    public function with() {
        return [
            'eventos' => Evento::orderBy('titulo')->get(),
            'presencas' => Presenca::with(['inscricao.evento', 'inscricao.participante'])
                ->when($this->evento_id, function($query) {
                    $query->whereHas('inscricao', function($q) {
                        $q->where('evento_id', $this->evento_id);
                    });
                })
                ->latest('data_checkin')
                ->paginate(5),
        ];
    }
}; ?>

<div class="w-full">
    
    {{-- HEADER FIXO (STICKY) --}}
    <div class="sticky top-0 z-20 bg-gray-50/95 dark:bg-[#09090b]/95 backdrop-blur-md px-6 py-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-row justify-between items-center gap-4">
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-blue-800 dark:text-blue-500 uppercase truncate">CONTROLO DE PRESENÇAS</h1>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">Selecione um evento para listar os presentes.</p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                {{-- IMPRIMIR LISTA --}}
                @if($evento_id)
                    <button wire:click="exportar" type="button" class="inline-flex items-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-full text-xs md:text-sm px-3 py-2 md:px-4 md:py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 cursor-pointer whitespace-nowrap transition-colors">
                        <svg class="w-4 h-4 mr-1 sm:mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M8 3a2 2 0 0 0-2 2v3h12V5a2 2 0 0 0-2-2H8Zm-2 7a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h1v-4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v4h1a2 2 0 0 0 2-2v5a2 2 0 0 0-2-2H6Zm3 4a1 1 0 0 0-1 1v5h8v-5a1 1 0 0 0-1-1H9Z" clip-rule="evenodd"/>
                        </svg>
                        <span class="hidden sm:inline">Imprimir Lista</span>
                        <span class="sm:hidden">Imprimir</span>
                    </button>
                @endif

                {{-- NOVO CHECK-IN --}}
                @canany(['criar_presencas'])
                    <a href="{{ route('presencas.create') }}" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-xs md:text-sm px-4 py-2 md:px-5 md:py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 shadow-lg shadow-blue-500/20 transition-all cursor-pointer whitespace-nowrap">
                        <span class="hidden sm:inline">Novo Check-in</span>
                        <span class="sm:hidden">Check-in</span>
                        <svg class="w-4 h-4 ml-1 sm:ml-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7h-15" /></svg>
                    </a>
                @endcanany
            </div>
        </div>
    </div>

    {{-- CONTEÚDO ROLÁVEL --}}
    <div class="p-6 space-y-6">
        
        @if (session('erro'))
            <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 dark:bg-gray-800 dark:text-red-400 font-medium border border-red-200 dark:border-red-800 shadow-sm" role="alert">
                {{ session('erro') }}
            </div>
        @endif

        {{-- SELECT DE FILTRAGEM (FLOWBITE) --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm max-w-md">
            <label for="evento_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Filtrar por Evento</label>
            <select wire:model.live="evento_id" id="evento_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option value="">Todos os Eventos</option>
                @foreach ($eventos as $evento)
                    <option value="{{ $evento->id }}">{{ $evento->titulo }}</option>
                @endforeach
            </select>
        </div>

        {{-- TABELA DE DADOS --}}
        <div class="relative overflow-x-auto shadow-sm border border-gray-200 dark:border-gray-700 rounded-[2rem] bg-white dark:bg-gray-800">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-4">Participante</th>
                        <th scope="col" class="px-6 py-4">Evento</th>
                        <th scope="col" class="px-6 py-4">Horário de Entrada</th>
                        <th scope="col" class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($presencas as $presenca)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <th scope="row" class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $presenca->inscricao->participante->name }}
                            </th>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-xs font-medium">
                                    {{ $presenca->inscricao->evento->titulo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($presenca->data_checkin)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                 <a href="{{ route('presencas.edit', $presenca->id) }}" class="px-3 py-2 text-xs font-medium text-gray-700 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white cursor-pointer transition-colors">
                                    Editar
                                 </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 italic">
                                Nenhuma presença registada para este critério.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINAÇÃO --}}
        <div class="mt-4">
            {{ $presencas->links() }}
        </div>
    </div>

    {{-- BOTÃO VOLTAR AO TOPO (Mobile) --}}
    <div class="md:hidden">
        <button id="btnVoltarTopoPres" x-on:click="const main = document.querySelector('main'); if(main) main.scrollTo({ top: 0, behavior: 'smooth' })" type="button" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 p-3.5 text-white bg-blue-600 rounded-full shadow-2xl hover:bg-blue-700 active:scale-95 transition-all focus:outline-none dark:bg-blue-500 dark:hover:bg-blue-600 border border-white/10" style="display: none;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"></path></svg>
        </button>
    </div>

    <script>
        function initScrollPres() {
            const main = document.querySelector('main');
            const btn = document.getElementById('btnVoltarTopoPres');
            if (main && btn) {
                main.removeEventListener('scroll', handlerPres);
                main.addEventListener('scroll', handlerPres);
            }
        }
        function handlerPres() {
            const main = document.querySelector('main');
            const btn = document.getElementById('btnVoltarTopoPres');
            if(main && btn) btn.style.display = main.scrollTop > 300 ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', initScrollPres);
        document.addEventListener('livewire:navigated', initScrollPres);
    </script>
</div>