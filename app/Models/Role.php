<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

// Ao estender SpatieRole, você herda automaticamente o relacionamento correto
// com a tabela 'role_has_permissions' e a trait HasPermissions.
class Role extends SpatieRole
{
    // O preenchimento do guard_name é necessário para evitar erros de valor padrão
    protected $fillable = [
        'name',
        'guard_name',
        'updated_at',
        'created_at',
    ];
}