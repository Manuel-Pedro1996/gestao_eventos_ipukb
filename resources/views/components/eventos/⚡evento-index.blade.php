<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\Inscricao;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Notifications\InscricaoConfirmadaNotification;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $searchDate = '';
    public $searchVagas = false;

    public function updating()
    {
        $this->resetPage();
    }

    public function rendering($view){
        $view->title('Eventos');
    }

    public function inscrever($eventoId)
    {
        $evento = Evento::findOrFail($eventoId);
        $user = Auth::user();

        if ($evento->data_fim && $evento->data_fim->isPast()) {
            session()->flash('erro', 'Este evento já foi encerrado.');
            return;
        }

        if ($evento->vagas_disponiveis <= 0) {
            session()->flash('erro', 'Sem vagas disponíveis.');
            return;
        }

        $existe = Inscricao::where('participante_id', auth()->id())
            ->where('evento_id', $eventoId)
            ->exists();

        if ($existe) {
            session()->flash('erro', 'Já estás inscrito neste evento.');
            return;
        }

        // CORREÇÃO 1: Guardar a instância da inscrição criada na variável $inscricao
        $inscricao = Inscricao::create([
            'participante_id' => auth()->id(),
            'evento_id' => $eventoId,
            'codigo_qr' => 'QR-' . strtoupper(Str::random(10)) . '-' . date('Y'),
            'data_inscricao' => now(),
        ]);

        $evento->decrement('vagas_disponiveis');
        session()->flash('success', 'Inscrição realizada com sucesso!');
        
        // CORREÇÃO 2: Passar os dois argumentos necessários ($evento e $inscricao)
        $user->notify(new InscricaoConfirmadaNotification($evento, $inscricao));
    }

    public function deleteEvento($eventoId)
    {   
        if (! auth()->user()->can('excluir_eventos')) {
            abort(403, 'Ação não autorizada.');
        }
        
        $evento = Evento::findOrFail($eventoId);
        $temInscricoes = Inscricao::where('evento_id', $eventoId)->exists();

        if ($temInscricoes) {
            session()->flash('erro', 'Não é possível deletar um evento com inscrições.');
            return;
        }

        $evento->delete();
        session()->flash('success', 'Evento eliminado com sucesso!');
    }

    public function with()
    {
        $userId = auth()->id();

        $query = Evento::query()
            ->addSelect([
                'inscricao_id' => Inscricao::select('id')
                    ->where('participante_id', $userId)
                    ->whereColumn('evento_id', 'eventos.id')
                    ->limit(1)
            ]);

        if ($this->search) {
            $query->where('eventos.titulo', 'like', '%' . $this->search . '%');
        }

        if ($this->searchDate) {
            $query->whereDate('eventos.data_evento', $this->searchDate);
        }

        if ($this->searchVagas) {
            $query->where('eventos.vagas_disponiveis', '>', 0);
        }

        return [
            'eventos' => $query
                ->orderByRaw('inscricao_id IS NULL DESC')
                ->orderByRaw('data_fim > NOW() DESC')
                ->orderByRaw('vagas_disponiveis > 0 DESC')
                ->latest('eventos.created_at')
                ->paginate(6),
        ];
    }
};
?>

<div class="w-full">
    
    {{-- HEADER FIXO (STICKY) --}}
    <div class="sticky top-0 z-10 bg-gray-50/80 dark:bg-[#09090b]/80 backdrop-blur-md px-6 pt-6 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-blue-800 dark:text-blue-500 uppercase">EVENTOS</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tecnologia e Programação.</p>
            </div>
            
            @can('criar_eventos')      
                <a href="{{ route('eventos.create') }}" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 shadow-lg shadow-blue-500/20 transition-all cursor-pointer">
                    Novo Evento
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </a>
            @endcan
        </div>
    </div>

    {{-- CONTEÚDO ROLÁVEL --}}
    <div class="p-6 space-y-6">

        {{-- BARRA DE PESQUISA ESTILIZADA (Flowbite) --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col lg:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label for="search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pesquisar</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input wire:model.live="search" type="text" id="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Pesquisar por título do evento..." />
                </div>
            </div>
            
            <div class="w-full lg:w-48">
                <label for="searchDate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Filtrar por data</label>
                <input wire:model.live="searchDate" type="date" id="searchDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
            </div>

            <div class="flex items-center h-11 px-2">
                <label class="inline-flex items-center cursor-pointer select-none">
                    <input wire:model.live="searchVagas" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600" />
                    <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Apenas com vagas</span>
                </label>
            </div>
        </div>

        {{-- MENSAGENS DE NOTIFICAÇÃO --}}
        @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-xl bg-green-50 dark:bg-gray-800 dark:text-green-400 font-medium border border-green-200 dark:border-green-800 shadow-sm flex items-center gap-2" role="alert">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('erro'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 dark:bg-gray-800 dark:text-red-400 font-medium border border-red-200 dark:border-red-800 shadow-sm flex items-center gap-2" role="alert">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                {{ session('erro') }}
            </div>
        @endif

        {{-- GRID DE EVENTOS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($eventos as $evento)
                @php $jaInscrito = !is_null($evento->inscricao_id); @endphp

                <div class="group flex flex-col border border-gray-200 dark:border-gray-700 shadow-md hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 rounded-[2rem] bg-white dark:bg-gray-800 overflow-hidden">
                    
                    {{-- BANNER COM OVERLAY --}}
<div class="relative w-full h-48 overflow-hidden bg-gray-100 dark:bg-gray-700">
    <img src="{{ $evento->foto ?? 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=600' }}" 
         class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
         alt="Banner do Evento">
    
    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
    
    <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
        @if($jaInscrito)
            <span class="bg-emerald-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-lg flex items-center gap-1 uppercase">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Inscrito
            </span>
        @endif
        
        <span class="{{ $evento->vagas_disponiveis > 0 ? 'bg-blue-600' : 'bg-red-600' }} text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-lg uppercase">
            {{ $evento->vagas_disponiveis }} Vagas
        </span>
    </div>
</div>

                    {{-- CONTEÚDO --}}
                    <div class="p-5 flex flex-col flex-1 gap-3">
                        <div class="space-y-1">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-700 dark:group-hover:text-blue-500 transition-colors">{{ $evento->titulo }}</h2>
                            <div class="flex items-center gap-1 text-gray-400 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="truncate">{{ $evento->local }}</span>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                           {{ $evento->descricao }}
                        </p>

                        {{-- RODAPÉ DO CARD --}}
                        <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-xs font-bold text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y') }}
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                @if ($evento->data_fim && $evento->data_fim->isPast())
                                    <button disabled class="w-full text-gray-400 bg-gray-100 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-700 dark:text-gray-500">
                                        Evento Encerrado
                                    </button>
                                @elseif ($evento->vagas_disponiveis <= 0 && !$jaInscrito)
                                    <button disabled class="w-full text-red-500 bg-red-50 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-900/30 dark:text-red-400">
                                        Lotado
                                    </button>
                                @elseif ($jaInscrito)
                                    <button disabled class="w-full text-emerald-800 bg-emerald-50 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-emerald-900/20 dark:text-emerald-400">
                                        Inscrição Confirmada
                                    </button>
                                @else
                                    <button wire:click="inscrever({{ $evento->id }})" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 shadow-md shadow-blue-500/20 transition-colors cursor-pointer">
                                        Inscrever-se Agora
                                    </button>
                                @endif

                                @canany(['editar_eventos', 'excluir_eventos'])
                                <div class="flex gap-2 border-t border-gray-100 dark:border-gray-700 mt-2 pt-2">
                                    @can('editar_eventos')
                                        <a href="{{ route('eventos.edit', $evento->id) }}" class="flex-1 inline-flex items-center justify-center py-2 px-3 text-xs font-medium text-gray-700 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            Editar
                                        </a>
                                    @endcan
                                    @can('excluir_eventos')
                                        <button wire:click="deleteEvento({{ $evento->id }})" class="flex-1 inline-flex items-center justify-center py-2 px-3 text-xs font-medium text-red-600 bg-white rounded-lg border border-gray-200 hover:bg-red-50 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-red-900/20 cursor-pointer transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Deletar
                                        </button>
                                    @endcan
                                </div>
                                @endcanany
                            </div>
                        </div>
                    </div> 
                </div>
            @endforeach
        </div>

        {{-- PAGINAÇÃO --}}
        <div class="mt-8">
            {{ $eventos->links() }}
        </div>
    </div>

    {{-- BOTÃO VOLTAR AO TOPO --}}
    <div class="md:hidden">
        <button id="btnVoltarTopo"
                style="display: none;"
                x-on:click="const main = document.querySelector('main'); if(main) main.scrollTo({ top: 0, behavior: 'smooth' })"
                type="button"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 p-3.5 text-white bg-blue-600 rounded-full shadow-2xl hover:bg-blue-700 active:scale-95 transition-all focus:outline-none dark:bg-blue-500 dark:hover:bg-blue-600 border border-white/10"
                aria-label="Voltar ao topo">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"></path>
            </svg>
        </button>
    </div>

    <script>
        function inicializarBotaoTopo() {
            const mainElement = document.querySelector('main');
            if (mainElement) {
                mainElement.removeEventListener('scroll', controlarVisibilidadeBotao);
                mainElement.addEventListener('scroll', controlarVisibilidadeBotao);
            }
        }

        function controlarVisibilidadeBotao() {
            const mainElement = document.querySelector('main');
            const btnTopo = document.getElementById('btnVoltarTopo');
            if (mainElement && btnTopo) {
                if (mainElement.scrollTop > 300) {
                    btnTopo.style.display = 'block';
                } else {
                    btnTopo.style.display = 'none';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', inicializarBotaoTopo);
        document.addEventListener('livewire:navigated', inicializarBotaoTopo);
    </script>
</div>