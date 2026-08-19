<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Inscricao;
use App\Notifications\InscricaoConfirmadaNotification;
use App\Notifications\InscricaoRejeitadaNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public ?int $inscricaoParaRejeitar = null;
    public string $motivoRejeicao = '';

    public function aprovar($inscricaoId)
    {
        abort_unless(auth()->user()->can('validar_pagamentos'), 403);

        $resultado = DB::transaction(function () use ($inscricaoId) {
            $inscricao = Inscricao::lockForUpdate()->findOrFail($inscricaoId);

            if ($inscricao->status !== 'pendente') {
                return null;
            }

            $evento = $inscricao->evento()->lockForUpdate()->first();

            if ($evento->vagas_disponiveis <= 0) {
                session()->flash('erro', 'Sem vagas disponíveis para confirmar esta inscrição.');
                return null;
            }

            $inscricao->update([
                'status' => 'confirmada',
                'codigo_qr' => 'QR-' . strtoupper(Str::random(10)) . '-' . date('Y'),
                'avaliado_por' => auth()->id(),
                'avaliado_em' => now(),
            ]);

            $evento->decrement('vagas_disponiveis');

            return [$evento, $inscricao];
        });

        if ($resultado) {
            [$evento, $inscricao] = $resultado;

            try {
                $inscricao->participante->notify(new InscricaoConfirmadaNotification($evento, $inscricao));
            } catch (\Throwable $e) {
                report($e);
                session()->flash('erro', 'Inscrição aprovada, mas houve falha ao enviar o email de confirmação.');
                return;
            }

            session()->flash('success', 'Inscrição aprovada.');
        }
    }

    public function abrirRejeicao($inscricaoId)
    {
        $this->inscricaoParaRejeitar = $inscricaoId;
        $this->motivoRejeicao = '';
    }

    public function confirmarRejeicao()
    {
        abort_unless(auth()->user()->can('validar_pagamentos'), 403);

        $this->validate([
            'motivoRejeicao' => 'nullable|string|max:500',
        ]);

        $inscricao = Inscricao::findOrFail($this->inscricaoParaRejeitar);

        if ($inscricao->status !== 'pendente') {
            $this->inscricaoParaRejeitar = null;
            return;
        }

        $inscricao->update([
            'status' => 'rejeitada',
            'observacao_avaliacao' => $this->motivoRejeicao ?: null,
            'avaliado_por' => auth()->id(),
            'avaliado_em' => now(),
        ]);

        $inscricao->participante->notify(new InscricaoRejeitadaNotification($inscricao->evento, $inscricao));

        $this->inscricaoParaRejeitar = null;
        session()->flash('success', 'Inscrição rejeitada.');
    }

    public function with()
    {
        return [
            'inscricoes' => Inscricao::with(['evento', 'participante'])
                ->where('status', 'pendente')
                ->latest('data_inscricao')
                ->paginate(10),
        ];
    }
};
?>

<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-blue-800 dark:text-blue-500">Comprovativos Pendentes</h1>

    @if (session('success'))
        <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400">{{ session('success') }}</div>
    @endif
    @if (session('erro'))
        <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">{{ session('erro') }}</div>
    @endif

    <div class="space-y-4">
        @forelse ($inscricoes as $inscricao)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex flex-col md:flex-row gap-4 items-start">
                <a href="{{ Storage::url($inscricao->comprovativo) }}" target="_blank" class="shrink-0">
                    <img src="{{ Storage::url($inscricao->comprovativo) }}" class="w-32 h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                </a>

                <div class="flex-1">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $inscricao->evento->titulo }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $inscricao->participante->name }} — {{ $inscricao->participante->email }}</p>
                    <p class="text-xs text-gray-400 mt-1">Enviado em {{ $inscricao->data_inscricao->format('d/m/Y H:i') }}</p>
                </div>

                <div class="flex gap-2 items-center">
                    {{-- Botão Aprovar (Fundo verde e texto branco garantidos por CSS inline) --}}
                    <button wire:click="aprovar({{ $inscricao->id }})" 
                        wire:confirm="Confirmar aprovação desta inscrição?"
                        style="background-color: #16a34a !important; color: #ffffff !important;"
                        class="font-medium rounded-lg text-sm px-4 py-2 hover:opacity-90 focus:ring-4 focus:ring-green-300 transition-all shadow-sm">
                        Aprovar
                    </button>

                    {{-- Botão Rejeitar (Fundo rosa/vermelho claro e texto vermelho escuro) --}}
                    <button wire:click="abrirRejeicao({{ $inscricao->id }})"
                        style="background-color: #fee2e2 !important; color: #dc2626 !important;"
                        class="font-medium rounded-lg text-sm px-4 py-2 hover:bg-red-200 transition-all shadow-sm">
                        Rejeitar
                    </button>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Não há comprovativos pendentes.</p>
        @endforelse
    </div>

    <div>{{ $inscricoes->links() }}</div>

    {{-- Modal simples de motivo de rejeição --}}
    @if ($inscricaoParaRejeitar)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Motivo da rejeição (opcional)</h2>
                <textarea wire:model="motivoRejeicao" rows="3"
                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Ex: Comprovativo ilegível, valor não corresponde..."></textarea>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('inscricaoParaRejeitar', null)" class="text-sm text-gray-500 px-4 py-2">Cancelar</button>
                    <button wire:click="confirmarRejeicao" class="text-white bg-red-600 hover:bg-red-700 rounded-lg text-sm px-4 py-2">Confirmar Rejeição</button>
                </div>
            </div>
        </div>
    @endif
</div>