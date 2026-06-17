<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatitiePermission;

class Permission extends SpatitiePermission
{   
    protected $fillable = [
        'name',
        'guard_name',
    ];
    
}
