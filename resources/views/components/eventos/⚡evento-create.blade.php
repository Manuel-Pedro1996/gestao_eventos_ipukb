<?php

use Livewire\Component;
use App\Models\Evento;
use Livewire\WithFileUploads;
// Importações necessárias para o SDK Nativo do Cloudinary
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
            'foto' => 'nullable|image|max:2048', // Máximo 2MB
        ]);

        $caminhoFoto = null;

        // Se o utilizador submeteu uma foto, fazemos o upload direto para o Cloudinary
        if ($this->foto) {
            // 1. Inicializa as configurações com a variável guardada no teu .env
            Configuration::instance(env('CLOUDINARY_URL'));

            // 2. Executa o upload usando o caminho real temporário do ficheiro no Fedora
            $resultado = (new UploadApi())->upload($this->foto->getRealPath(), [
                'folder' => 'eventos' // Organiza os banners dentro de uma pasta na cloud
            ]);

            // 3. Captura a URL pública e segura (HTTPS) gerada
            $caminhoFoto = $resultado['secure_url'];
        }

        Evento::create([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'local' => $this->local,
            'data_evento' => $this->data_evento,
            'data_fim' => $this->data_fim,
            'capacidade_maxima' => $this->capacidade_maxima,
            'vagas_disponiveis' => $this->vagas_disponiveis, // Corrigido para associar o input real
            'foto' => $caminhoFoto, // Salva a URL completa (ex: https://res.cloudinary.com/...)
            'organizador_id' => auth()->id(),
        ]);

        return redirect()->route('eventos.index')
            ->with('success', 'Evento criado com sucesso e imagem salva na nuvem!');
    }
};
?>

<div class="p-6 w-full max-w-2xl mx-auto">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Criar Evento</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Preencha os dados abaixo para publicar o evento.</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('eventos.index') }}" class="w-full text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
        </div>
    </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <form wire:submit="salvar" enctype="multipart/form-data" class="space-y-5">
        
        {{-- Área de Upload de Foto (Flowbite) --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Banner do Evento</label>
            <div class="flex items-center justify-center w-full">
                @if ($foto)
                    <div class="relative w-full h-48 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                        <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                        <button type="button" wire:click="$set('foto', null)" class="absolute top-2 right-2 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full text-xs p-1.5 text-center inline-flex items-center shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @else
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 border-gray-300 dark:border-gray-600 dark:hover:bg-gray-500">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">Clique para carregar a foto do evento</p>
                        </div>
                        <input type="file" wire:model="foto" class="hidden" accept="image/*" />
                    </label>
                @endif
            </div>
            @error('foto') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Título --}}
        <div>
            <label for="titulo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Título</label>
            <input wire:model="titulo" type="text" id="titulo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Ex: Jornadas Científicas" />
            @error('titulo') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        {{-- Descrição --}}
        <div>
            <label for="descricao" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descrição</label>
            <textarea wire:model="descricao" id="descricao" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Detalhes do evento..."></textarea>
            @error('descricao') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        {{-- Grid de Inputs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="local" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Local</label>
                <input wire:model="local" type="text" id="local" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Auditório Central" />
                @error('local') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="data_evento" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Início</label>
                <input wire:model="data_evento" type="datetime-local" id="data_evento" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                @error('data_evento') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="data_fim" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Término</label>
                <input wire:model="data_fim" type="datetime-local" id="data_fim" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                @error('data_fim') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="capacidade_maxima" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Capacidade</label>
                <input wire:model="capacidade_maxima" type="number" id="capacidade_maxima" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                @error('capacidade_maxima') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Vagas Iniciais --}}
        <div>
            <label for="vagas_disponiveis" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Vagas Iniciais</label>
            <input wire:model="vagas_disponiveis" type="number" id="vagas_disponiveis" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
            @error('vagas_disponiveis') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Botão Submeter --}}
        @can('criar_eventos')
            <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center inline-flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Criar Evento
            </button>
        @endcan

    </form>
</div>