<?php

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

new class extends Component
{
   use WithPagination;
   public $search= '';

   public function updatingSearch(){
        $this->resetPage();
   }
   
   public function rendering($view){
        $view->title('NovoUsuários');
    }

   public function deleteUser($userId){
    if(auth()->user()->can('eliminar_users')){
        abort(403, "Acesso Negado");
    }

    $user = User::findOrFail($userId);
    $user->delete();
   }

   public function with(){
        return [
            'users' =>User::where(function ($query){
                $query->where('name', 'Like', '%' . $this->search . '%')
                ->orWhere('email', 'Like', '%' . $this->search . '%') 
                ->orWhere('id', 'Like', '%' . $this->search . '%');
            })

            ->orWhereHas('roles', function ($query){
               $query->where('name', 'Like', '%' . $this->search . '%');
            })->latest()->paginate(5),
        ];
   }
};
?>

<div p-6 w-full>
    <div class="flex flex-colum md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-amber-700">Usuários</h1>
            <p class="text-sm text-gray-500 dark:text-amber-300 mt-1">Lista de Usuários</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="search" id="default-search" class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-amber-700 dark:border-gray-600 dark:placeholder-green-400 dark:text-green-400 dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Buscar usuários..." />
            </div>
            @canany(['criar_users', 'criar_qualquer_users'])
            <a href="{{ route('users.create') }}" class="text-gray-900 bg-gradient-to-r from-red-200 via-red-300 to-yellow-200 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-red-100 dark:focus:ring-red-400 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center gap-2">
                Novo Usuário
            </a>
            @endcanany
        </div>

    </div>
    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-red-700">

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-amber-950 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Nome</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Cargo</th>
                    <th scope="col" class="px-6 py-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr class="odd:bg-white odd:dark:bg-amber-900 even:bg-gray-50 even:dark:bg-amber-800 border-b dark:border-amber-700 border-gray-200">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $user->name }}
                    </th>
                    <td class="px-6 py-4 ">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @foreach ($user->roles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-red-700 dark:text-gray-300">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 flex items-center gap-2">
                        @canany(['editar_users', 'editar_qualquer_users'])
                        <a href="{{ route('users.edit', $user->id) }}" class="px-3 py-2 text-xs font-medium text-center text-white bg-blue-700 rounded-lg ...">Editar</a>
                        @endcanany
                        @canany(['eliminar_users'])
                        <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Confirmar exclusão" type="button" class="px-3 py-2 text-xs font-medium text-center text-white bg-red-700 rounded-lg ...">Deletar</button>
                        @endcanany
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
   <div class="p-4">
        {{ $users->links() }}
    </div>
</div>