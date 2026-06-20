<?php

use Livewire\Component;
use App\Models\Inscricao;
use App\Models\Presenca;
use App\Notifications\PresencaConfirmadaNotification;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $codigo_qr = '';

    public function registrarCheckin()
    {
        $inscricao = Inscricao::with(['user', 'evento'])->where('codigo_qr', $this->codigo_qr)->first();

        if (!$inscricao) {
            session()->flash('error', '⚠️ Código QR inválido. Tente novamente.');
            $this->dispatch('checkin-completed', status: 'error');
            return;
        }

        $presencaExistente = Presenca::where('inscricao_id', $inscricao->id)->exists();

        if ($presencaExistente) {
            session()->flash('error', '🚫 Este participante já fez check-in!');
            $this->codigo_qr = '';
            $this->dispatch('checkin-completed', status: 'error');
            return;
        }

        Presenca::create([
            'inscricao_id' => $inscricao->id,
            'data_checkin' => now()
        ]);

        if ($inscricao->user) {
            $inscricao->user->notify(new PresencaConfirmadaNotification($inscricao->evento));
        }

        session()->flash('success', '✅ Check-in realizado com sucesso!');
        $this->codigo_qr = '';
        
        $this->dispatch('checkin-completed', status: 'success');
    }
}; ?>

<div class="p-6 w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800">Check-in de Evento</h1>
            <p class="text-sm text-gray-500 mt-1">Registe a entrada do participante através do Código QR</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            @canany(['visualizar_presencas'])
            <a href="{{ route('presencas.index') }}" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none transition-colors cursor-pointer text-center">
                Voltar
            </a>
            @endcanany
        </div>
   </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <div id="alerts-container">
        @if (session()->has('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 text-center font-medium border border-green-200 dark:border-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 text-center font-medium border border-red-200 dark:border-red-800 animate-pulse">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <section class="space-y-6">
        <div class="flex justify-center bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
            <div id="reader" wire:ignore class="w-full max-w-[300px] overflow-hidden rounded-lg aspect-square bg-black"></div>
        </div>

        <form wire:submit="registrarCheckin" class="space-y-5">
            <div>
                <label for="codigo_qr" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Código QR</label>
                <input 
                    wire:model="codigo_qr" 
                    type="text" 
                    id="codigo_qr" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                    placeholder="Introduza ou digitalize o código..." 
                    autofocus
                />
            </div>

            @canany(['criar_presencas'])
            <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none transition-colors cursor-pointer">
                Confirmar Presença
            </button>
            @endcanany
        </form>
    </section>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            let html5QrCode = new Html5Qrcode("reader");
            let isProcessing = false;

            const config = { fps: 10, qrbox: { width: 220, height: 220 } };

            // Inicia o stream da câmara traseira automaticamente sem botões adicionais
            html5QrCode.start(
                { facingMode: "environment" }, 
                config,
                (decodedText) => {
                    if (isProcessing) return;
                    
                    isProcessing = true; // Trava o scanner para não duplicar requisições
                    
                    @this.set('codigo_qr', decodedText);
                    @this.call('registrarCheckin');
                },
                (errorMessage) => { /* Tratamento silencioso de frames vazios */ }
            ).catch(err => console.error("Erro ao iniciar câmara:", err));

            // Quando o Livewire termina de processar a resposta 200 que vimos nos logs
            window.addEventListener('checkin-completed', (event) => {
                isProcessing = false;
                
                // Limpa os alertas após 4 segundos nativamente
                setTimeout(() => {
                    const container = document.getElementById('alerts-container');
                    if (container) container.innerHTML = '';
                }, 4000);
            });
        });
    </script>
</div>