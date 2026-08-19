<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Evento;
use App\Models\Inscricao;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

new class extends Component
{
    use WithFileUploads;

    public Evento $evento;
    public $comprovativo;

    public string $banco = '';
    public string $referencia_pagamento = '';
    public $valor_pago = '';
    public string $data_pagamento = '';

    public array $bancos = [
        'BAI', 'BFA', 'BIC', 'BPC', 'Millennium Atlântico',
        'Standard Bank', 'Banco Sol', 'Banco YETU', 'Banco Económico', 'Outro',
    ];

    public function mount(Evento $evento)
    {
        if (! $evento->pago) {
            abort(404);
        }

        $existe = Inscricao::where('participante_id', auth()->id())
            ->where('evento_id', $evento->id)
            ->whereIn('status', ['pendente', 'confirmada'])
            ->exists();

        if ($existe) {
            abort(403, 'Já tens uma inscrição ativa ou pendente neste evento.');
        }

        $this->evento = $evento;
        $this->data_pagamento = now()->format('Y-m-d');
    }

    public function enviar()
    {
        $this->validate([
            'comprovativo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'banco' => 'required|string|max:100',
            'referencia_pagamento' => 'nullable|string|max:100',
            'valor_pago' => 'required|numeric|min:0.01',
            'data_pagamento' => 'required|date|before_or_equal:today',
        ]);

        if ($this->evento->vagas_disponiveis <= 0) {
            session()->flash('erro', 'Sem vagas disponíveis.');
            return;
        }

        if ((float) $this->valor_pago < (float) $this->evento->preco) {
            session()->flash('erro', 'O valor informado é inferior ao preço do evento ('
                . number_format($this->evento->preco, 2) . ' Kz). Verifica o comprovativo.');
            return;
        }

        $hash = hash_file('sha256', $this->comprovativo->getRealPath());

        // Pré-checagem em PHP: rápida, dá mensagem amigável na maioria dos casos.
        // Não elimina a corrida sozinha — é só a primeira linha de defesa, mais barata que
        // deixar sempre o upload do ficheiro acontecer antes de sabermos que vai falhar.
        $inscricaoExistente = Inscricao::where('participante_id', auth()->id())
            ->where('evento_id', $this->evento->id)
            ->where('status', 'rejeitada')
            ->first();

        $hashDuplicado = Inscricao::where('comprovativo_hash', $hash)
            ->when($inscricaoExistente, fn ($q) => $q->where('id', '!=', $inscricaoExistente->id))
            ->exists();

        if ($hashDuplicado) {
            session()->flash('erro', 'Este comprovativo já está associado a outra inscrição. Cada comprovativo só pode ser usado uma vez.');
            return;
        }

        if ($this->referencia_pagamento) {
            $referenciaDuplicada = Inscricao::where('banco', $this->banco)
                ->where('referencia_pagamento', $this->referencia_pagamento)
                ->where('data_pagamento', $this->data_pagamento)
                ->when($inscricaoExistente, fn ($q) => $q->where('id', '!=', $inscricaoExistente->id))
                ->exists();

            if ($referenciaDuplicada) {
                session()->flash('erro', 'Esta referência de pagamento já está associada a outra inscrição. Confirma o número no teu comprovativo antes de tentar de novo.');
                return;
            }
        }

        $caminho = $this->comprovativo->store('comprovativos', 'public');

        $dadosPagamento = [
            'status' => 'pendente',
            'comprovativo' => $caminho,
            'comprovativo_hash' => $hash,
            'banco' => $this->banco,
            'referencia_pagamento' => $this->referencia_pagamento ?: null,
            'valor_pago' => $this->valor_pago,
            'data_pagamento' => $this->data_pagamento,
            'observacao_avaliacao' => null,
            'avaliado_por' => null,
            'avaliado_em' => null,
            'data_inscricao' => now(),
        ];

        // Camada real anti-corrida: a transação + índices únicos da BD.
        // Se duas submissões concorrentes chegarem aqui ao mesmo tempo com o mesmo
        // hash ou a mesma referência, o MariaDB só deixa uma delas gravar — a outra
        // recebe um erro de chave duplicada, que capturamos abaixo.
        try {
            DB::transaction(function () use ($inscricaoExistente, $dadosPagamento) {
                if ($inscricaoExistente) {
                    $inscricaoExistente->update($dadosPagamento);
                } else {
                    Inscricao::create(array_merge($dadosPagamento, [
                        'participante_id' => auth()->id(),
                        'evento_id' => $this->evento->id,
                    ]));
                }
            });
        } catch (QueryException $e) {
            // Apaga o ficheiro já gravado no disco, já que a inscrição não foi criada
            \Illuminate\Support\Facades\Storage::disk('public')->delete($caminho);

            if ($e->getCode() === '23000') {
                if (str_contains($e->getMessage(), 'comprovativo_hash')) {
                    session()->flash('erro', 'Este comprovativo já está associado a outra inscrição. Cada comprovativo só pode ser usado uma vez.');
                } elseif (str_contains($e->getMessage(), 'inscricoes_pagamento_unico')) {
                    session()->flash('erro', 'Esta referência de pagamento já está associada a outra inscrição.');
                } else {
                    session()->flash('erro', 'Não foi possível processar o teu envio. Tenta novamente.');
                }
                return;
            }

            throw $e;
        }

        return redirect()->route('eventos.index')
            ->with('success', 'Comprovativo enviado. Aguarda a aprovação do organizador.');
    }
};
?>

<div class="p-6 w-full max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-800 dark:text-blue-500">Enviar Comprovativo</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Evento: <strong>{{ $evento->titulo }}</strong> — Valor: {{ number_format($evento->preco, 2) }} Kz
    </p>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    @if (session('erro'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
            {{ session('erro') }}
        </div>
    @endif

    <form wire:submit="enviar" class="space-y-4">
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Comprovativo (imagem ou PDF)</label>
            <input type="file" wire:model="comprovativo" accept="image/*,.pdf"
                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600" />
            @error('comprovativo') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror

            @if ($comprovativo && str_starts_with($comprovativo->getMimeType(), 'image'))
                <img src="{{ $comprovativo->temporaryUrl() }}" class="mt-3 w-full max-h-64 object-contain rounded-lg border border-gray-200 dark:border-gray-700">
            @endif
        </div>

        <div>
            <label for="banco" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Banco / Canal de Pagamento</label>
            <select wire:model="banco" id="banco" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Seleciona o banco...</option>
                @foreach ($bancos as $b)
                    <option value="{{ $b }}">{{ $b }}</option>
                @endforeach
            </select>
            @error('banco') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="valor_pago" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Valor Pago (Kz)</label>
                <input wire:model="valor_pago" type="number" step="0.01" min="0" id="valor_pago" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="{{ number_format($evento->preco, 2) }}" />
                @error('valor_pago') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="data_pagamento" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Data do Pagamento</label>
                <input wire:model="data_pagamento" type="date" id="data_pagamento" max="{{ now()->format('Y-m-d') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('data_pagamento') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label for="referencia_pagamento" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                Nº de Transação / Referência <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input wire:model="referencia_pagamento" type="text" id="referencia_pagamento" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Ex: 60201 24020 07159 87351" />
            <p class="text-xs text-gray-400 mt-1">Copia o número que aparece como "Transacção", "Referência", "Tr" ou "ID" no teu comprovativo.</p>
            @error('referencia_pagamento') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5" wire:loading.attr="disabled" wire:target="enviar,comprovativo">
            <span wire:loading.remove wire:target="enviar">Enviar Comprovativo</span>
            <span wire:loading wire:target="enviar">A enviar...</span>
        </button>

        <a href="{{ route('eventos.index') }}" class="block text-center text-sm text-gray-500 hover:underline">Cancelar</a>
    </form>
</div>