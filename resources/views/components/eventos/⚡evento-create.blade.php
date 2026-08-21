<?php

use Livewire\Component;
use App\Models\Evento;
use Livewire\WithFileUploads;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

new class extends Component
{
    use WithFileUploads;

    public string $titulo = '';
    public string $descricao = '';
    public string $local = '';
    public string $data_evento = '';
    public string $data_fim = '';
    public int $vagas_disponiveis = 0;
    public int $capacidade_maxima = 0;
    public $foto;

    public bool $pago = false;
    public ?string $preco = null;

    public function rendering($view)
    {
        $view->title('Criar Evento');
    }

    public function salvar()
    {
        $this->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'local' => 'required|string',
            'data_evento' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_evento',
            'vagas_disponiveis' => 'required|integer|min:0',
            'capacidade_maxima' => 'required|integer|min:1',
            'foto' => 'nullable|image|max:2048',
            'pago' => 'boolean',
            'preco' => 'required_if:pago,true|nullable|numeric|min:0.01',
        ]);

        $caminhoFoto = null;

        if ($this->foto) {
            Configuration::instance(env('CLOUDINARY_URL'));

            $resultado = (new UploadApi())->upload($this->foto->getRealPath(), [
                'folder' => 'eventos'
            ]);

            $caminhoFoto = $resultado['secure_url'];
        }

        Evento::create([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'local' => $this->local,
            'data_evento' => $this->data_evento,
            'data_fim' => $this->data_fim,
            'capacidade_maxima' => $this->capacidade_maxima,
            'vagas_disponiveis' => $this->vagas_disponiveis,
            'foto' => $caminhoFoto,
            'organizador_id' => auth()->id(),
            'pago' => $this->pago,
            'preco' => $this->pago ? $this->preco : null,
        ]);

        return redirect()->route('eventos.index')
            ->with('success', 'Evento criado com sucesso e imagem salva na nuvem!');
    }
};
?>

<div class="w-full space-y-6">

    {{-- HEADER FIXO (STICKY) --}}
   <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Novo Evento</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Criar novo evento</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('eventos.index') }}" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
        </div>
   </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">
    <div class="w-full">

        <form wire:submit="salvar" enctype="multipart/form-data" class="space-y-6">
            
            {{-- BANNER DO EVENTO --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Banner do Evento</label>
                <div class="flex items-center justify-center w-full">
                    @if ($foto)
                        <div class="relative w-full h-48 rounded-2xl overflow-hidden border border-gray-300 dark:border-gray-600 shadow-sm">
                            <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                            <button type="button" wire:click="$set('foto', null)" class="absolute top-2 right-2 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full text-xs p-1.5 text-center inline-flex items-center shadow cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @else
                        <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-2xl cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 border-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Clique para carregar a foto do evento</p>
                            </div>
                            <input type="file" wire:model="foto" class="hidden" accept="image/*" />
                        </label>
                    @endif
                </div>
                @error('foto') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- TÍTULO --}}
            <div>
                <label for="titulo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Título</label>
                <input wire:model="titulo" type="text" id="titulo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Ex: Jornadas Científicas 2026" />
                @error('titulo') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>
            
            {{-- DESCRIÇÃO --}}
            <div>
                <label for="descricao" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descrição</label>
                <textarea wire:model="descricao" id="descricao" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-xl border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Detalhes e programação do evento..."></textarea>
                @error('descricao') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>
            
            {{-- LOCAL E DATAS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="local" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Local</label>
                    <input wire:model="local" type="text" id="local" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Auditório Central" />
                    @error('local') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="data_evento" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Início</label>
                    <input wire:model="data_evento" type="datetime-local" id="data_evento" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    @error('data_evento') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="data_fim" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Término</label>
                    <input wire:model="data_fim" type="datetime-local" id="data_fim" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    @error('data_fim') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- VAGAS E CAPACIDADE --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="capacidade_maxima" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Capacidade Máxima</label>
                    <input wire:model="capacidade_maxima" type="number" id="capacidade_maxima" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    @error('capacidade_maxima') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="vagas_disponiveis" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Vagas Iniciais</label>
                    <input wire:model="vagas_disponiveis" type="number" id="vagas_disponiveis" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    @error('vagas_disponiveis') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- TIPO DE EVENTO --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-200 dark:border-gray-600 space-y-3">
                <label class="inline-flex items-center cursor-pointer select-none">
                    <input wire:model.live="pago" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-700 dark:border-gray-600" />
                    <span class="ml-2 text-sm font-bold text-gray-900 dark:text-white">Este é um evento pago</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Se marcado, os participantes deverão submeter um comprovativo bancário para validação.
                </p>

                @if ($pago)
                    <div class="pt-2">
                        <label for="preco" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Preço do Ingressos (Kz)</label>
                        <input wire:model="preco" type="number" step="0.01" min="0" id="preco" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Ex: 5000.00" />
                        @error('preco') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            {{-- SUBMIT --}}
            <div class="pt-2">
                @can('criar_eventos')
                    <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-bold rounded-xl text-sm px-5 py-3 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-all cursor-pointer shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Criar Evento
                    </button>
                @endcan
            </div>

        </form>
    </div>

    {{-- BOTÃO VOLTAR AO TOPO (Mobile) --}}
    <div class="md:hidden">
        <button id="btnVoltarTopoCriarEvento" x-on:click="const main = document.querySelector('main'); if(main) main.scrollTo({ top: 0, behavior: 'smooth' })" type="button" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 p-3.5 text-white bg-blue-600 rounded-full shadow-2xl hover:bg-blue-700 active:scale-95 transition-all focus:outline-none dark:bg-blue-500 dark:hover:bg-blue-600 border border-white/10" style="display: none;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"></path></svg>
        </button>
    </div>

    <script>
        function initScrollCriarEvento() {
            const main = document.querySelector('main');
            const btn = document.getElementById('btnVoltarTopoCriarEvento');
            if (main && btn) {
                main.removeEventListener('scroll', handlerCriarEvento);
                main.addEventListener('scroll', handlerCriarEvento);
            }
        }
        function handlerCriarEvento() {
            const main = document.querySelector('main');
            const btn = document.getElementById('btnVoltarTopoCriarEvento');
            if(main && btn) btn.style.display = main.scrollTop > 300 ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', initScrollCriarEvento);
        document.addEventListener('livewire:navigated', initScrollCriarEvento);
    </script>
</div>