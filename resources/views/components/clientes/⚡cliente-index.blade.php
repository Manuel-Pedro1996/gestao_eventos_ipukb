<?php

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';
    
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function rendering($view){
        $view->title('Clientes');
    }

    public function deleteClient($userId){
        if (! auth()->user()->can('excluir_clientes')) {
            abort(403, 'Ação não autorizada.');
        }
        $user = User::findOrFail($userId);
        $user->delete();
        session()->flash('success', 'Cliente eliminado com sucesso!');
    }

    public function with()
    {
        return [
            'clientes' => User::doesntHave('roles') 
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(5),
        ];
    }
};
?>

<div class="w-full"> 

    {{-- HEADER FIXO (STICKY) --}}
    <div class="sticky top-0 z-10 bg-gray-50/95 dark:bg-[#09090b]/95 backdrop-blur-md px-6 py-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-row justify-between items-center gap-4">
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-blue-800 dark:text-blue-500 uppercase truncate">CLIENTES</h1>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">Utilizadores sem papel definido (Participantes).</p>    
            </div> 

            @canany(['criar_users', 'criar_qualquer_users'])
            <div class="shrink-0">
                <a href="{{ route('cliente.create') }}" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-xs md:text-sm px-4 py-2 md:px-5 md:py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 shadow-lg shadow-blue-500/20 transition-all cursor-pointer whitespace-nowrap">
                    <span class="hidden sm:inline">Novo Cliente</span>
                    <span class="sm:hidden">Novo</span>
                    <svg class="w-4 h-4 ml-1 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </a>
            </div>
            @endcanany
        </div>
    </div>

    {{-- CONTEÚDO ROLÁVEL --}}
    <div class="p-6 space-y-6">

        @if (session('success'))
            <div class="p-4 text-sm text-green-800 rounded-xl bg-green-50 dark:bg-gray-800 dark:text-green-400 font-medium border border-green-200 dark:border-green-800 shadow-sm flex items-center gap-2">
                {{ session('success') }}
            </div>
        @endif

        {{-- BARRA DE PESQUISA (FLOWBITE) --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm max-w-md">
            <label for="client-search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pesquisar Cliente</label>
            <div class="relative w-full">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="search" id="client-search" class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Pesquisar clientes..." />
            </div>
        </div>

        {{-- TABELA DE DADOS --}}
        <div class="relative overflow-x-auto shadow-sm border border-gray-200 dark:border-gray-700 rounded-[2rem] bg-white dark:bg-gray-800">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-4">Nome</th>
                        <th scope="col" class="px-6 py-4">Email</th>
                        <th scope="col" class="px-6 py-4">Estatuto</th>
                        <th scope="col" class="px-6 py-4 w-48 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($clientes as $cliente)
                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <th scope="row" class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $cliente->name }}
                        </th>
                        <td class="px-6 py-4 dark:text-gray-300">{{ $cliente->email }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-emerald-800 bg-emerald-100 rounded-full dark:bg-emerald-900/40 dark:text-emerald-300">
                                Cliente
                            </span>
                        </td>
                        <td class="px-6 py-4 flex items-center justify-end gap-2">
                            @canany(['editar_users', 'editar_qualquer_users'])
                            <a href="{{ route('cliente.edit', $cliente->id) }}" class="px-3 py-2 text-xs font-medium text-gray-700 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                Editar
                            </a>
                            @endcanany
                            
                            @can('eliminar_users')
                            <button wire:click="deleteClient({{ $cliente->id }})" wire:confirm="Tens a certeza que desejas eliminar este cliente?" type="button" class="px-3 py-2 text-xs font-medium text-red-600 bg-white rounded-lg border border-gray-200 hover:bg-red-50 dark:bg-gray-800 dark:text-red-500 dark:border-gray-600 dark:hover:bg-red-900/20 cursor-pointer transition-colors">
                                Deletar
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 italic">
                            Nenhum cliente encontrado sem papel definido.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINAÇÃO --}}
        <div class="mt-4">
            {{ $clientes->links() }}
        </div>
    </div>

    {{-- BOTÃO VOLTAR AO TOPO (Mobile) --}}
    <div class="md:hidden">
        <button id="btnVoltarTopoClientes" x-on:click="const main = document.querySelector('main'); if(main) main.scrollTo({ top: 0, behavior: 'smooth' })" type="button" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 p-3.5 text-white bg-blue-600 rounded-full shadow-2xl hover:bg-blue-700 active:scale-95 transition-all focus:outline-none dark:bg-blue-500 dark:hover:bg-blue-600 border border-white/10" style="display: none;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"></path></svg>
        </button>
    </div>

    <script>
        function initScrollClientes() {
            const main = document.querySelector('main');
            const btn = document.getElementById('btnVoltarTopoClientes');
            if (main && btn) {
                main.removeEventListener('scroll', handlerClientes);
                main.addEventListener('scroll', handlerClientes);
            }
        }
        function handlerClientes() {
            const main = document.querySelector('main');
            const btn = document.getElementById('btnVoltarTopoClientes');
            if(main && btn) btn.style.display = main.scrollTop > 300 ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', initScrollClientes);
        document.addEventListener('livewire:navigated', initScrollClientes);
    </script>
</div>