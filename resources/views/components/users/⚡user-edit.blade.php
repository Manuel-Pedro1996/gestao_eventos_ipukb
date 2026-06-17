<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Role;


new class extends Component
{
    public User $user;
    public string $name= '';
    public string $email= '';
    public string|null $password=null;
    public $selectedRoles = [];
    public $allRoles = []; 

   public function rules(): array
   {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255',Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => ['sometimes','nullable','string','min:8'],
   ];
   }

   public function mount(User $user)
   {
        $this->user = $user;
        $this->name =$user->name;
        $this->email =$user->email;
        $this->selectedRoles = $user->roles()->pluck('name')->toArray();
        $this->allRoles = Role::whereNot('name', 'super_admin')->pluck('name')->toArray();
   }

   public function updateUser()
   {
        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'password' => empty($this->password) ? $this->user->password : bcrypt($this->password),
        ]);

        $this->user->syncRoles($this->selectedRoles);
        //session()->flash('message', 'Usuário criado com sucesso!');

        $this->reset(['name', 'email', 'password']);
        return redirect()->route('users.index');
   }

};
?>
<div class="p-6 w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Editar Usuário</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Alterar um usuário</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            @canany(['visualizar_roles'])
            <a href="{{ route('users.index') }}" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
            @endcanany
        </div>
   </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <section class="w-full" x-data="{ 
                all: {{ json_encode($allRoles) }},
                selected: @entangle('selectedRoles')
             }">
        <form wire:submit="updateUser" class="space-y-5">
            
            <div>
                <label for="edit-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
                <input 
                    wire:model="name" 
                    type="text" 
                    id="edit-name" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Digite o nome completo" 
                    required
                />
                @error('name') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="edit-email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <input 
                    wire:model="email" 
                    type="email" 
                    id="edit-email" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="email@example.com" 
                    required
                />
                @error('email') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>
 
            <div>
                <label for="edit-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Senha</label>
                <input 
                    wire:model="password" 
                    type="password" 
                    id="edit-password" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Deixe em branco para não alterar" 
                />
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Mantenha vazio caso prefira manter a senha atual.</p>
                @error('password') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="w-full">
                 <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cargos</label>
                
                <div class="flex items-center mt-2 mb-3">
                    <input 
                        type="checkbox" 
                        id="select-all-permissions" 
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600 cursor-pointer"
                        :checked="selected.length === all.length"
                        @change="selected = $el.checked ? [...all] : []"
                    >
                    <label for="select-all-permissions" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 cursor-pointer select-none">
                        Selecionar Todas
                    </label>
                </div>

                <hr class="h-px my-3 bg-gray-100 border-0 dark:bg-gray-800">

                <div class="flex flex-wrap gap-x-6 gap-y-4">
                    @foreach ($allRoles as $role)
                        <div class="flex items-center">
                            <input 
                                wire:model="selectedRoles" 
                                id="role-{{ $role }}" 
                                type="checkbox" 
                                value="{{ $role }}" 
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600 cursor-pointer shrink-0"
                            >
                            <label for="role-{{ $role }}" class="ms-2 text-sm font-normal text-gray-900 dark:text-gray-300 cursor-pointer select-none whitespace-nowrap">
                                {{ $role }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('selectedRoles') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-2">
                @canany(['editar_users', 'editar_qualquer_users'])
                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer">
                    Salvar Alterações
                </button>
                @endcanany
            </div>
        </form>
    </section>
</div>