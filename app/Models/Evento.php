<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'data_evento',
        'data_fim',
        'local',
        'vagas_disponiveis', 
        'capacidade_maxima',
        'foto',
        'organizador_id',
    ];

    protected $casts = [
    'data_evento' => 'datetime',
    'data_fim' => 'datetime',
];

    public function organizador()
    {
        return $this->belongsTo(User::class, 'organizador_id');
    }
    public function inscricoes() {
    return $this->hasMany(Inscricao::class);
}

}
