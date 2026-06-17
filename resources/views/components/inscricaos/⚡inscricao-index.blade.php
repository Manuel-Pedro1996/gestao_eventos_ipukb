<?php

use Livewire\Component;
use App\Models\Inscricao;
use Livewire\WithPagination;
use App\Notifications\InscricaoCanceladaNotification;
use Illuminate\Support\Facades\Auth;

new class extends Component
{   
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public function rendering($view){
        $view->title('Inscrições');
    }

    public function deletarInscricao($inscricaoId) {
        $inscricao = Inscricao::with('evento')->findOrFail($inscricaoId);
        $user = Auth::user();
        $evento = $inscricao->evento;

        if ($evento) {
            $evento->increment('vagas_disponiveis');
        }

        $inscricao->delete();

        if ($evento) {
            $user->notify(new InscricaoCanceladaNotification($evento));
        }
    }

    public function with() {
        return [
            'inscricaos' => Inscricao::where('participante_id', auth()->id())
                ->with(['evento', 'presenca'])
                ->latest()
                ->paginate(6),
        ];
    }
};
?>

<div class="w-full">
    
    {{-- HEADER FIXO (STICKY) --}}
    <div class="sticky top-0 z-20 bg-gray-50/95 dark:bg-[#09090b]/95 backdrop-blur-md px-6 py-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-row justify-between items-center gap-4">
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-blue-800 dark:text-blue-500 uppercase truncate">MINHAS INSCRIÇÕES</h1>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">Eventos onde confirmou participação.</p>    
            </div>
            
            <div class="shrink-0">
                <a href="{{ route('eventos.index') }}" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-xs md:text-sm px-4 py-2 md:px-5 md:py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 shadow-lg shadow-blue-500/20 transition-all cursor-pointer whitespace-nowrap">
                    <span class="hidden sm:inline">Nova Inscrição</span>
                    <span class="sm:hidden">Nova</span>
                    <svg class="w-4 h-4 ml-1 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- CONTEÚDO ROLÁVEL --}}
    <div class="p-6 space-y-6" wire:poll.5s>

        {{-- GRID DE INSCRIÇÕES --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($inscricaos as $inscricao)
                {{-- Card Estilo Flowbite --}}
                <div class="p-5 flex flex-col justify-between bg-white rounded-[2rem] border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700 min-h-[320px] hover:shadow-xl transition-all duration-300">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $inscricao->evento->titulo }}</h2>
                        <div class="flex items-center gap-1 text-gray-400 text-xs mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="truncate">{{ $inscricao->evento->local }}</span>
                        </div>
                    </div>

                    <div class="my-4 flex flex-col items-center justify-center flex-1">
                        @if($inscricao->presenca)
                            {{-- ESTADO: PRESENÇA CONFIRMADA --}}
                            <div class="w-full p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl text-center flex flex-col items-center justify-center animate-fade-in">
                                <svg class="w-8 h-8 text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-emerald-800 dark:text-emerald-400 text-sm font-bold block">Check-in Concluído!</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Aproveite o evento.</p>
                            </div>
                        @else
                            {{-- ESTADO: PENDENTE (MOSTRA QR) --}}
                            <div class="flex flex-col items-center space-y-3 w-full">
                                <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center dark:bg-white">
                                    @php
                                        try {
                                            $options = new \chillerlan\QRCode\QROptions([
                                                'version'      => 5,
                                                'outputType'   => 'png',
                                                'eccLevel'     => \chillerlan\QRCode\Common\EccLevel::L,
                                                'scale'        => 4,
                                                'addQuietzone' => true,
                                            ]);
                                            $qrImage = (new \chillerlan\QRCode\QRCode($options))->render($inscricao->codigo_qr);
                                        } catch (\Exception $e) {
                                            $qrImage = null;
                                        }
                                    @endphp
                                    
                                    @if($qrImage)
                                        <img src="{{ $qrImage }}" alt="QR Code" class="w-32 h-32 md:w-36 md:h-36">
                                    @else
                                        <div class="w-32 h-32 flex items-center justify-center text-red-500 text-xs border border-dashed border-red-200">
                                            Erro no QR Code
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="text-center">
                                    <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Código de Verificação</span>
                                    <span class="text-xs font-mono font-bold text-gray-800 dark:text-gray-200">{{ $inscricao->codigo_qr }}</span>
                                </div>
                                
                               
                                <button 
                                    wire:click="deletarInscricao({{ $inscricao->id }})" 
                                    wire:confirm="Queres cancelar esta inscrição?" 
                                    class="w-full text-red-600 bg-red-50 hover:bg-red-100 focus:ring-4 focus:ring-red-200 font-medium rounded-xl text-xs px-3 py-2.5 text-center dark:bg-gray-700 dark:text-red-400 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                                >
                                    Cancelar Inscrição
                                </button>
                           
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PAGINAÇÃO --}}
        <div class="mt-6">
            {{ $inscricaos->links() }}
        </div>
    </div>

    {{-- BOTÃO VOLTAR AO TOPO (Mobile) --}}
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

    {{-- SCRIPT DE MÓDULO ÚNICO --}}
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