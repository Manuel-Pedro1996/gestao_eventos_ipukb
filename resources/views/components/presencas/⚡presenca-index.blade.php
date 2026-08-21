<?php

use Livewire\Component;
use App\Models\Presenca;
use App\Models\Evento;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $eventoSelecionado = null;
    public $searchInscrito = '';

    public function rendering($view){
        $view->title('Presenças por Evento');
    }

    public function abrirModalInscritos($eventoId)
    {
        $this->eventoSelecionado = Evento::find($eventoId);
        $this->searchInscrito = '';
        $this->resetPage('modalPage');
    }

    public function fecharModal()
    {
        $this->eventoSelecionado = null;
    }

    public function updatingSearchInscrito()
    {
        $this->resetPage('modalPage');
    }

    public function exportar($eventoId)
    {
        return redirect()->route('presencas.imprimir', ['evento' => $eventoId]);
    }

    public function with()
    {
        $eventosQuery = Evento::whereHas('inscricoes.presenca')
            ->withCount(['inscricoes as total_presencas' => function ($q) {
                $q->whereHas('presenca');
            }])
            ->addSelect(['ultimo_checkin' => Presenca::select('data_checkin')
                ->whereColumn('inscricoes.evento_id', 'eventos.id')
                ->join('inscricoes', 'inscricoes.id', '=', 'presencas.inscricao_id')
                ->latest('data_checkin')
                ->limit(1)
            ])
            ->orderByDesc('ultimo_checkin');

        $presencasModal = collect();
        if ($this->eventoSelecionado) {
            $presencasModal = Presenca::with(['inscricao.participante'])
                ->whereHas('inscricao', function ($q) {
                    $q->where('evento_id', $this->eventoSelecionado->id);
                    if ($this->searchInscrito) {
                        $q->whereHas('participante', function ($partQuery) {
                            $partQuery->where('name', 'like', '%' . $this->searchInscrito . '%')
                                      ->orWhere('email', 'like', '%' . $this->searchInscrito . '%');
                        });
                    }
                })
                ->latest('data_checkin')
                ->paginate(5, ['*'], 'modalPage');
        }

        return [
            'eventos' => $eventosQuery->paginate(10),
            'presencasModal' => $presencasModal,
        ];
    }
}; ?>

<div class="w-full">
    
    {{-- HEADER FIXO (STICKY) --}}
    <div class="sticky top-0 z-10 bg-gray-50/95 dark:bg-[#09090b]/95 backdrop-blur-md px-6 py-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-row justify-between items-center gap-4">
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-blue-800 dark:text-blue-500 uppercase truncate">CONTROLO DE PRESENÇAS</h1>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">Lista de eventos com presenças confirmadas.</p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
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

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="p-6 space-y-6">
        
        @if (session('erro'))
            <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 dark:bg-gray-800 dark:text-red-400 font-medium border border-red-200 dark:border-red-800 shadow-sm" role="alert">
                {{ session('erro') }}
            </div>
        @endif

        {{-- TABELA DE EVENTOS --}}
        <div class="relative overflow-x-auto shadow-sm border border-gray-200 dark:border-gray-700 rounded-[2rem] bg-white dark:bg-gray-800">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-4">Evento</th>
                        <th scope="col" class="px-6 py-4 text-center">Presenças Confirmadas</th>
                        <th scope="col" class="px-6 py-4">Último Check-in</th>
                        <th scope="col" class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($eventos as $evento)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <th scope="row" class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $evento->titulo }}
                            </th>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-blue-100 text-blue-900 dark:bg-blue-900/80 dark:text-blue-200 rounded-full text-xs font-bold">
                                    {{ $evento->total_presencas }} {{ Str::plural('presente', $evento->total_presencas) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $evento->ultimo_checkin ? \Carbon\Carbon::parse($evento->ultimo_checkin)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-1">
                                <button wire:click="exportar({{ $evento->id }})" type="button" class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                                    Imprimir
                                </button>
                                <button wire:click="abrirModalInscritos({{ $evento->id }})" type="button" class="px-3 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                    Ver Inscritos
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 italic">
                                Nenhum evento com presenças registadas até ao momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINAÇÃO DE EVENTOS --}}
        <div class="mt-4">
            {{ $eventos->links() }}
        </div>
    </div>

    {{-- MODAL DE INSCRITOS / PRESENÇAS DO EVENTO --}}
    @if ($eventoSelecionado)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-4xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-2xl my-8">
                
                {{-- CABEÇALHO DA MODAL --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            Inscritos Presentes: {{ $eventoSelecionado->titulo }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Gerencie a lista de presença e faça edições necessárias.</p>
                    </div>
                    <button wire:click="fecharModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg text-sm p-1.5 inline-flex items-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    {{-- PESQUISA DENTRO DA MODAL --}}
                    <div class="max-w-xs">
                        <input wire:model.live.debounce.300ms="searchInscrito" type="search" class="block w-full p-2.5 text-xs text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Pesquisar participante..." />
                    </div>

                    {{-- TABELA DE PRESENÇAS --}}
                    <div class="relative overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Participante</th>
                                    <th scope="col" class="px-6 py-3">Email</th>
                                    <th scope="col" class="px-6 py-3">Horário de Entrada</th>
                                    <th scope="col" class="px-6 py-3 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($presencasModal as $presenca)
                                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <th scope="row" class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                            {{ $presenca->inscricao->participante->name }}
                                        </th>
                                        <td class="px-6 py-3 text-xs">
                                            {{ $presenca->inscricao->participante->email }}
                                        </td>
                                        <td class="px-6 py-3 text-xs text-gray-600 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($presenca->data_checkin)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <a href="{{ route('presencas.edit', $presenca->id) }}" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:text-white transition-colors">
                                                Editar
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400 italic text-xs">
                                            Nenhum participante encontrado para os critérios.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINAÇÃO DA MODAL --}}
                    @if($presencasModal->hasPages())
                        <div class="mt-2">
                            {{ $presencasModal->links() }}
                        </div>
                    @endif
                </div>

                {{-- RODAPÉ DA MODAL --}}
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end">
                    <button wire:click="fecharModal" type="button" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif

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