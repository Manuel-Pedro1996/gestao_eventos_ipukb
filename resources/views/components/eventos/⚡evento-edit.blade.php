<?php

use Livewire\Component;
use App\Models\Evento;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public Evento $evento;
    public string $titulo = '';
    public string $descricao = '';
    public string $local = '';
    public $data_evento = '';
    public $data_fim = '';
    public int $vagas_disponiveis = 0;
    public int $capacidade_maxima = 0;
    
    // Deixamos 'foto' para o novo upload e 'fotoAtual' para exibir o que já existe
    public $foto;
    public $fotoAtual;

    public function mount(Evento $evento)
    {
        $this->evento = $evento;
        $this->titulo = $evento->titulo;
        $this->descricao = $evento->descricao;
        $this->local = $evento->local;
        $this->data_evento = $evento->data_evento->format('Y-m-d\TH:i');
        $this->data_fim = $evento->data_fim ? $evento->data_fim->format('Y-m-d\TH:i') : '';
        $this->vagas_disponiveis = $evento->vagas_disponiveis;
        $this->capacidade_maxima = $evento->capacidade_maxima;
        $this->fotoAtual = $evento->foto; // Guardamos o caminho que vem do banco
    }

    public function atualizar()
    {
        $this->validate([
            'titulo' => 'required|string',
            'descricao' => 'required|string',
            'local' => 'required|string',
            'data_evento' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_evento',
            'vagas_disponiveis' => 'required|integer|min:0',
            'capacidade_maxima' => 'required|integer|min:1',
            'foto' => 'nullable|image|max:2048', // Valida apenas se houver novo upload
        ]);

        $dados = [
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'local' => $this->local,
            'data_evento' => $this->data_evento,
            'data_fim' => $this->data_fim,
            'vagas_disponiveis' => $this->vagas_disponiveis,
            'capacidade_maxima' => $this->capacidade_maxima,
        ];

        // Lógica da Foto:
        if ($this->foto) {
            // Se subir uma nova, apaga a antiga do disco para não encher o Fedora de lixo
            if ($this->fotoAtual) {
                Storage::disk('public')->delete($this->fotoAtual);
            }
            $dados['foto'] = $this->foto->store('eventos', 'public');
        }

        $this->evento->update($dados);

        return redirect()->route('eventos.index')
            ->with('success', 'Evento atualizado com sucesso!');
    }
};
?>

<div class="p-6 w-full max-w-2xl mx-auto">
    {{-- Cabeçalho --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Editar Evento</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Altere as informações do evento: {{ $evento->titulo }}</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('eventos.index') }}" class="w-full text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
        </div>
    </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <form wire:submit="atualizar"  enctype="multipart/form-data" class="space-y-5">
        
        {{-- Área de Upload e Gestão do Banner (Flowbite) --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Banner do Evento</label>
            <div class="flex items-center justify-center w-full">
                
                {{-- Caso 1: Utilizador selecionou uma foto nova temporária --}}
                @if ($foto instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                    <div class="relative w-full h-48 rounded-lg overflow-hidden border border-blue-500 group">
                        <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                        
                        <label class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity duration-200">
                            <span class="text-white font-medium text-sm">Trocar Imagem</span>
                            <input type="file" wire:model="foto" class="hidden" accept="image/*" />
                        </label>

                        <button type="button" wire:click="$set('foto', null)" class="absolute top-2 right-2 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full text-xs p-1.5 text-center inline-flex items-center shadow z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                {{-- Caso 2: Já existe uma foto salva no banco --}}
                @elseif ($fotoAtual)
                    <label class="relative w-full h-48 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 cursor-pointer group block">
                        <img src="{{ asset('storage/' . $fotoAtual) }}" class="w-full h-full object-cover transition duration-200 group-hover:brightness-75">
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Alterar Banner
                            </div>
                        </div>

                        <input type="file" wire:model="foto" class="hidden" accept="image/*" />
                    </label>

                {{-- Caso 3: Não tem foto nenhuma --}}
                @else
                    <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 border-gray-300 dark:border-gray-600 dark:hover:border-gray-500">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">Clique para adicionar foto</p>
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
            <input wire:model="titulo" type="text" id="titulo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
            @error('titulo') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        {{-- Descrição --}}
        <div>
            <label for="descricao" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Descrição</label>
            <textarea wire:model="descricao" id="descricao" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
            @error('descricao') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        {{-- Grid de Inputs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="local" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Local</label>
                <input wire:model="local" type="text" id="local" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
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
                <label for="capacidade_maxima" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Capacidade Máxima</label>
                <input wire:model="capacidade_maxima" type="number" id="capacidade_maxima" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                @error('capacidade_maxima') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Vagas Atuais --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <label for="vagas_disponiveis" class="text-sm font-medium text-gray-900 dark:text-white">Vagas Atuais</label>
                <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2 py-0.5 rounded-sm dark:bg-orange-900 dark:text-orange-300">Cuidado ao alterar</span>
            </div>
            <input wire:model="vagas_disponiveis" type="number" id="vagas_disponiveis" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
            @error('vagas_disponiveis') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Botões de Acção --}}
        <div class="flex gap-3 pt-2">
            <a href="{{ route('eventos.index') }}" class="flex-1 text-center text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition-colors cursor-pointer">
                Cancelar
            </a>
            <button type="submit" class="flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer inline-flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Guardar Alterações
            </button>
        </div>
    </form>
</div>