<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presenca extends Model
{
    protected $fillable = [
        'inscricao_id',
        'data_checkin',
    ];

    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }
    
}
