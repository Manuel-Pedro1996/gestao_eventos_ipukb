<?php

use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;

new class extends Component
{
    public Permission $permission; 
    public $name;
    public $selectedRoles = []; 
    public $allRoles = [];

   public function mount(Permission $permission)
    {
        $this->permission = $permission;
        $this->name = $permission->name;
        $this->selectedRoles = $permission->roles()->pluck('name')->toArray();
        $this->allRoles = Role::whereNot('name', 'super_admin')->pluck('name')->toArray();
    }

   protected function rules()
   {
      return [
            'name' => ['required', 'string', "unique:permissions,name,{$this->permission->id}"],
            'selectedRoles' => 'array',
        ];
   }

   public function updatePermission()
   {
        $this->validate();

         $this->permission->update([
                'name' => $this->name,
          ]);

        $this->permission->syncRoles($this->selectedRoles);

        $this->name = '';
        $this->selectedRoles = []; // Corrigido aqui (estava selectedPermissions)
        return redirect()->route('permissions.index');
   }
};
?>

<div class="p-6 w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Editar Permissão</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Altere os dados da permissão e os cargos associados</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            @canany(['visualizar_permissions'])
            <a href="{{ route('permissions.index') }}" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
            @endcanany
        </div>
    </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <section class="w-full"
             x-data="{ 
                all: {{ json_encode($allRoles) }},
                selected: @entangle('selectedRoles')
             }">
        <form wire:submit="updatePermission" class="space-y-6">
            
            <div>
                <label for="edit-permission-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
                <input 
                    wire:model="name" 
                    type="text" 
                    id="edit-permission-name" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Digite o nome da permissão" 
                    required
                />
                @error('name') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="w-full">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cargos</label>
                
                <div class="flex items-center mt-2 mb-3">
                    <input 
                        type="checkbox" 
                        id="select-all-roles" 
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600 cursor-pointer"
                        :checked="selected.length === all.length"
                        @change="selected = $el.checked ? [...all] : []"
                    >
                    <label for="select-all-roles" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 cursor-pointer select-none">
                        Selecionar Todas
                    </label>
                </div>

                <hr class="h-px my-3 bg-gray-100 border-0 dark:bg-gray-800">

                <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
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
                @canany(['editar_permissions', 'editar_qualquer_permissions'])
                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer">
                    Salvar Alterações
                </button>
                @endcanany
            </div>

        </form>
    </section>
</div>