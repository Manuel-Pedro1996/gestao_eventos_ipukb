<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Inscricao;
use App\Notifications\InscricaoConfirmadaNotification;
use App\Notifications\InscricaoRejeitadaNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public ?Inscricao $inscricaoSelecionada = null;
    public ?int $inscricaoParaRejeitar = null;
    public string $motivoRejeicao = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function rendering($view)
    {
        $view->title('Comprovativos Pendentes');
    }

    public function abrirDetalhes($inscricaoId)
    {
        $this->inscricaoSelecionada = Inscricao::with(['evento', 'participante'])->find($inscricaoId);
    }

    public function fecharDetalhes()
    {
        $this->inscricaoSelecionada = null;
    }

    public function aprovar($inscricaoId)
    {
        abort_unless(auth()->user()->can('validar_pagamentos'), 403, 'Ação não autorizada.');

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
                session()->flash('erro', 'Inscrição aprovada, mas houve falha ao enviar o e-mail de confirmação.');
                return;
            }

            $this->fecharDetalhes();
            session()->flash('success', 'Inscrição aprovada com sucesso!');
        }
    }

    public function abrirRejeicao($inscricaoId)
    {
        $this->fecharDetalhes();
        $this->inscricaoParaRejeitar = $inscricaoId;
        $this->motivoRejeicao = '';
    }

    public function fecharRejeicao()
    {
        $this->inscricaoParaRejeitar = null;
        $this->motivoRejeicao = '';
    }

    public function confirmarRejeicao()
    {
        abort_unless(auth()->user()->can('validar_pagamentos'), 403, 'Ação não autorizada.');

        $this->validate([
            'motivoRejeicao' => 'nullable|string|max:500',
        ]);

        $inscricao = Inscricao::findOrFail($this->inscricaoParaRejeitar);

        if ($inscricao->status !== 'pendente') {
            $this->fecharRejeicao();
            return;
        }

        $inscricao->update([
            'status' => 'rejeitada',
            'observacao_avaliacao' => $this->motivoRejeicao ?: null,
            'avaliado_por' => auth()->id(),
            'avaliado_em' => now(),
        ]);

        try {
            $inscricao->participante->notify(new InscricaoRejeitadaNotification($inscricao->evento, $inscricao));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->fecharRejeicao();
        session()->flash('success', 'Inscrição rejeitada com sucesso!');
    }

    public function with()
    {
        return [
            'inscricoes' => Inscricao::with(['evento', 'participante'])
                ->where('status', 'pendente')
                ->where(function ($query) {
                    $query->whereHas('participante', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('evento', function ($q) {
                        $q->where('titulo', 'like', '%' . $this->search . '%');
                    });
                })
                ->latest('data_inscricao')
                ->paginate(5),
        ];
    }
};
?>

<div class="w-full">
    <div class="sticky top-0 z-10 bg-gray-50/95 dark:bg-[#09090b]/95 backdrop-blur-md px-6 py-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-row justify-between items-center gap-4">
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-blue-800 dark:text-blue-500 uppercase truncate">COMPROVATIVOS PENDENTES</h1>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">Validação de pagamento e emissão de ingressos para participantes.</p>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if (session('success'))
            <div class="p-4 text-sm text-green-800 rounded-xl bg-green-50 dark:bg-gray-800 dark:text-green-400 font-medium border border-green-200 dark:border-green-800 shadow-sm flex items-center gap-2">
                {{ session('success') }}
            </div>
        @endif

        @if (session('erro'))
            <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 dark:bg-gray-800 dark:text-red-400 font-medium border border-red-200 dark:border-red-800 shadow-sm flex items-center gap-2">
                {{ session('erro') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm max-w-md">
            <label for="comprovativo-search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pesquisar Comprovativo</label>
            <div class="relative w-full">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="search" id="comprovativo-search" class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Pesquisar por participante ou evento..." />
            </div>
        </div>

        <div class="relative overflow-x-auto shadow-sm border border-gray-200 dark:border-gray-700 rounded-[2rem] bg-white dark:bg-gray-800">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-4">Comprovativo</th>
                        <th scope="col" class="px-6 py-4">Participante</th>
                        <th scope="col" class="px-6 py-4">Valor Pago</th>
                        <th scope="col" class="px-6 py-4 w-48 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($inscricoes as $inscricao)
                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            @if ($inscricao->comprovativo)
                                @php
                                    $urlComprovativo = str_starts_with($inscricao->comprovativo, 'http')
                                        ? $inscricao->comprovativo
                                        : Storage::url($inscricao->comprovativo);
                                    $extension = strtolower(pathinfo($inscricao->comprovativo, PATHINFO_EXTENSION));
                                    $isPdf = $extension === 'pdf' || str_contains($urlComprovativo, '.pdf');
                                @endphp

                                @if ($isPdf)
                                    <button wire:click="abrirDetalhes({{ $inscricao->id }})" type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-xl transition-all shadow-sm">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                                        </svg>
                                        Ver PDF
                                    </button>
                                @else
                                    <button wire:click="abrirDetalhes({{ $inscricao->id }})" type="button" class="inline-block relative group">
                                        <img src="{{ $urlComprovativo }}" alt="Comprovativo" class="w-12 h-12 object-cover rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm group-hover:opacity-80 transition-opacity">
                                    </button>
                                @endif
                            @else
                                <span class="text-xs text-gray-400 italic">Sem ficheiro</span>
                            @endif
                        </td>
                        <th scope="row" class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap dark:text-white">
                            <div>{{ $inscricao->participante->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">{{ $inscricao->participante->email }}</div>
                        </th>
                        <td class="px-6 py-4 text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                            {{ number_format($inscricao->valor_pago, 2) }} Kz
                        </td>
                        <td class="px-6 py-4 flex items-center justify-end gap-2">
                            <button wire:click="abrirDetalhes({{ $inscricao->id }})"
                                type="button"
                                class="px-3 py-2 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg shadow-sm transition-all cursor-pointer">
                                Verificar
                            </button>

                            @can('validar_pagamentos')
                            <button wire:click="aprovar({{ $inscricao->id }})" 
                                wire:confirm="Confirmar aprovação desta inscrição?"
                                type="button"
                                style="background-color: #16a34a !important; color: #ffffff !important;"
                                class="px-3 py-2 text-xs font-bold rounded-lg shadow-sm hover:opacity-90 focus:ring-4 focus:ring-green-300 transition-all cursor-pointer">
                                Aprovar
                            </button>

                            <button wire:click="abrirRejeicao({{ $inscricao->id }})"
                                type="button"
                                style="background-color: #fee2e2 !important; color: #dc2626 !important;"
                                class="px-3 py-2 text-xs font-bold rounded-lg shadow-sm hover:bg-red-200 transition-all cursor-pointer">
                                Rejeitar
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 italic">
                            Nenhum comprovativo pendente encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $inscricoes->links() }}
        </div>
    </div>

    @if ($inscricaoSelecionada)
        @php
            $urlModal = str_starts_with($inscricaoSelecionada->comprovativo, 'http')
                ? $inscricaoSelecionada->comprovativo
                : Storage::url($inscricaoSelecionada->comprovativo);
        @endphp
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-3xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-2xl my-8">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Verificar Comprovativo #{{ $inscricaoSelecionada->id }}
                    </h3>
                    <button wire:click="fecharDetalhes" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg text-sm p-1.5 inline-flex items-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[75vh] overflow-y-auto">
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 border-b border-gray-100 dark:border-gray-700 pb-2">
                            Dados do Pagamento
                        </h4>

                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Participante</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $inscricaoSelecionada->participante->name }}</span>
                                <span class="block text-xs text-gray-400">{{ $inscricaoSelecionada->participante->email }}</span>
                            </div>

                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Evento / Preço Esperado</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $inscricaoSelecionada->evento->titulo }}</span>
                                <span class="block text-xs font-bold text-blue-600 dark:text-blue-400">{{ number_format($inscricaoSelecionada->evento->preco, 2) }} Kz</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Banco / Canal</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $inscricaoSelecionada->banco ?? 'Não informado' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Valor Declarado</span>
                                    <span class="font-bold text-green-600 dark:text-green-400">{{ number_format($inscricaoSelecionada->valor_pago, 2) }} Kz</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Data de Pagamento</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ $inscricaoSelecionada->data_pagamento ? \Carbon\Carbon::parse($inscricaoSelecionada->data_pagamento)->format('d/m/Y') : '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Data de Envio</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ $inscricaoSelecionada->data_inscricao ? $inscricaoSelecionada->data_inscricao->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Nº Transação / Referência</span>
                                <span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-800 dark:text-gray-200">
                                    {{ $inscricaoSelecionada->referencia_pagamento ?? 'Nenhum informado' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 flex flex-col justify-between">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                                Documento do Comprovativo
                            </h4>

                            @if ($inscricaoSelecionada->comprovativo)
                                @php
                                    $isPdfModal = str_contains($urlModal, '.pdf') || strtolower(pathinfo($inscricaoSelecionada->comprovativo, PATHINFO_EXTENSION)) === 'pdf';
                                @endphp

                                @if ($isPdfModal)
                                    <div class="p-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 text-center">
                                        <svg class="w-12 h-12 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Este comprovativo é um ficheiro PDF.</p>
                                        <a href="{{ $urlModal }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all">
                                            Abrir PDF em nova aba
                                        </a>
                                    </div>
                                @else
                                    <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-900">
                                        <img src="{{ $urlModal }}" alt="Comprovativo" class="w-full max-h-64 object-contain mx-auto">
                                        <a href="{{ $urlModal }}" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                            Abrir imagem original ↗
                                        </a>
                                    </div>
                                @endif
                            @endif
                        </div>

                        @can('validar_pagamentos')
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
                            <button wire:click="aprovar({{ $inscricaoSelecionada->id }})" 
                                wire:confirm="Confirmar aprovação desta inscrição?"
                                type="button"
                                style="background-color: #16a34a !important; color: #ffffff !important;"
                                class="flex-1 py-2.5 text-xs font-bold rounded-xl shadow-sm hover:opacity-90 transition-all cursor-pointer text-center">
                                Aprovar Inscrição
                            </button>
                            <button wire:click="abrirRejeicao({{ $inscricaoSelecionada->id }})"
                                type="button"
                                style="background-color: #fee2e2 !important; color: #dc2626 !important;"
                                class="flex-1 py-2.5 text-xs font-bold rounded-xl shadow-sm hover:bg-red-200 transition-all cursor-pointer text-center">
                                Rejeitar
                            </button>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Confirmação de Rejeição --}}
    @if ($inscricaoParaRejeitar)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md overflow-hidden border border-gray-200 dark:border-gray-700 shadow-2xl p-6 space-y-4">
                <h3 class="text-lg font-bold text-red-600 dark:text-red-400">Rejeitar Inscrição</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Especifique o motivo da rejeição (opcional). Esta informação será enviada ao participante.
                </p>

                <div>
                    <textarea wire:model="motivoRejeicao" rows="3" class="w-full text-sm rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-3 focus:ring-red-500 focus:border-red-500" placeholder="Ex: Valor incorreto, comprovativo ilegível..."></textarea>
                    @error('motivoRejeicao') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="fecharRejeicao" type="button" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button wire:click="confirmarRejeicao" type="button" class="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700">
                        Confirmar Rejeição
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>