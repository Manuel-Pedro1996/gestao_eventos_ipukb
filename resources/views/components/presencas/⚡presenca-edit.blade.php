<?php

use Livewire\Component;
use App\Models\Presenca;

new class extends Component {
    public Presenca $presenca;
    public string $data_checkin;

    public function mount(Presenca $presenca) {
        $this->presenca = $presenca;
        $this->data_checkin = \Carbon\Carbon::parse($presenca->data_checkin)->format('Y-m-d\TH:i');
    }

    public function update()
{
    $this->validate([
        'data_checkin' => 'required|date'
    ]);

    $this->presenca->update([
        'data_checkin' => $this->data_checkin
    ]);

    session()->flash('success', 'Presença atualizada com sucesso!');

    return redirect()->route('presencas.index');
}
}; ?>

<div class="p-6 w-full">
     <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800"> Editar Presença</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ajustar horário de entrada para: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $presenca->inscricao->participante->name }}</span></p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            @canany(['visualizar_presencas'])
            <a href="{{ route('presencas.index') }}" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
            @endcanany
        </div>
   </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">
    
    <form wire:submit="update" class="space-y-5 max-w-md">
        <div>
            <label for="data_checkin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Data e Hora do Check-in</label>
            <input 
                wire:model="data_checkin" 
                type="datetime-local" 
                id="data_checkin" 
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                required
            />
            @error('data_checkin') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <div class="flex gap-2 pt-2">
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer">
                Atualizar
            </button>
            <a href="{{ route('presencas.index') }}" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>