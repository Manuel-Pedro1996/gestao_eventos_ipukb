<?php

use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;

new class extends Component
{
    public Role $role;
    public $name;
    public $selectedPermissions = [];
    public $allPermissions = [];


    public function mount(Role $role)
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
        $this->allPermissions = Permission::pluck('name')->toArray();
    }

   protected function rules()
   {
      return [
            'name' => ['required', 'string', "unique:roles,name,{$this->role->id}"],
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'string|exists:permissions,name',
        ];
   }

   public function updateRole()
   {
        $this->validate();

         $this->role->update([
                'name' => $this->name,
                'guard_name' => 'web',
          
          ]);
        $this->role->syncPermissions($this->selectedPermissions);

        $this->name = '';
        $this->selectedPermissions = []; 
        return redirect()->route('roles.index');
   }
};
?>

<div class="p-6 w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-blue-800 dark:text-blue-800">Editar cargo</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Altere as informações e permissões associadas a este papel.</p>    
        </div> 

        <div class="flex items-center gap-3 w-full md:w-auto">
            @canany(['visualizar_roles'])
            <a href="{{ route('roles.index') }}" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer text-center">
                Voltar
            </a>
            @endcanany
        </div>
   </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <section class="w-full" 
             x-data="{ 
                all: {{ json_encode($allPermissions) }},
                selected: @entangle('selectedPermissions')
             }">
        <form wire:submit="updateRole" class="space-y-5">
            
            <div>
                <label for="edit-role-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
                <input 
                    wire:model="name" 
                    type="text" 
                    id="edit-role-name" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                    placeholder="Digite o papel" 
                    required
                />
                @error('name') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Permissões</label>
                
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
                    @foreach ($allPermissions as $permission)
                        <div class="flex items-center">
                            <input 
                                wire:model="selectedPermissions" 
                                id="permission-{{ $permission }}" 
                                type="checkbox" 
                                value="{{ $permission }}" 
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600 cursor-pointer shrink-0"
                            >
                            <label for="permission-{{ $permission }}" class="ms-2 text-sm font-normal text-gray-900 dark:text-gray-300 cursor-pointer select-none whitespace-nowrap">
                                {{ $permission }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('selectedPermissions') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-2">
                @can(['editar_roles', 'editar_qualquer_roles'])
                <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 transition-colors cursor-pointer">
                    Salvar Alterações
                </button>
                @endcan
            </div>

        </form>
    </section>
</div>