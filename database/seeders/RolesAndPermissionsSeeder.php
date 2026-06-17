<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- ENTIDADE: USERS (Usuários) ---
        Permission::firstOrCreate(['name' => 'visualizar_qualquer_users']);
        Permission::firstOrCreate(['name' => 'visualizar_users']);
        Permission::firstOrCreate(['name' => 'criar_users']);
        Permission::firstOrCreate(['name' => 'editar_users']);
        Permission::firstOrCreate(['name' => 'eliminar_users']);

        // --- ENTIDADE: POSTS (Publicações) ---
        Permission::firstOrCreate(['name' => 'visualizar_qualquer_posts']);
        Permission::firstOrCreate(['name' => 'visualizar_posts']);
        Permission::firstOrCreate(['name' => 'criar_posts']);
        Permission::firstOrCreate(['name' => 'editar_posts']);
        Permission::firstOrCreate(['name' => 'eliminar_posts']);
        Permission::firstOrCreate(['name' => 'publicar_posts']);

        // --- ENTIDADE: ROLES (Papéis) ---
        Permission::firstOrCreate(['name' => 'visualizar_qualquer_roles']);
        Permission::firstOrCreate(['name' => 'visualizar_roles']);
        Permission::firstOrCreate(['name' => 'criar_roles']);
        Permission::firstOrCreate(['name' => 'editar_roles']);
        Permission::firstOrCreate(['name' => 'eliminar_roles']);

        // --- ENTIDADE: PERMISSIONS (Permissões) ---
        Permission::firstOrCreate(['name' => 'visualizar_qualquer_permissions']);
        Permission::firstOrCreate(['name' => 'visualizar_permissions']);
        Permission::firstOrCreate(['name' => 'criar_permissions']);
        Permission::firstOrCreate(['name' => 'editar_permissions']);
        Permission::firstOrCreate(['name' => 'eliminar_permissions']);

        Role::firstOrCreate(['name' => 'moderador'])
            ->givePermissionTo([
                'visualizar_qualquer_users',
                'visualizar_users',
                'criar_users',
                'editar_users',
                'eliminar_users',
                'visualizar_qualquer_posts',
                'visualizar_posts',
                'criar_posts',
                'editar_posts',
                'eliminar_posts',
                'publicar_posts',
            ]);

    }
}