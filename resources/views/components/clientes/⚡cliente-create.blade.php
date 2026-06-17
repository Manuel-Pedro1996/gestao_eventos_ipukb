<?php

use Livewire\Component;
use App\Models\User;

new class extends Component
{
    public $name;
    public $email;
    public $password;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function createUser()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        session()->flash('message', 'Cliente criado com sucesso!');
        $this->reset(['name', 'email', 'password']);
        
        return redirect()->route('cliente.index');
    }
};
?>

<div class="p-6 w-full">
   <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Novo Cliente</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Criar novo registo de cliente</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('cliente.index') }}" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
        </div>
   </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <section class="w-full">
        <form wire:submit="createUser" class="space-y-5">
            
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
                <input 
                    wire:model="name" 
                    type="text" 
                    id="name" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Digite o nome completo" 
                    required
                />
                @error('name') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <input 
                    wire:model="email" 
                    type="email" 
                    id="email" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="email@example.com" 
                    required
                />
                @error('email') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>
 
            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Senha</label>
                <input 
                    wire:model="password" 
                    type="password" 
                    id="password" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Digite a senha" 
                    required
                />
                @error('password') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-2">
                @canany(['criar_users', 'criar_qualquer_users'])
                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer">
                    Criar Cliente
                </button>
                @endcanany
            </div>
        </form>
    </section>
</div>