<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;


class Inscricao extends Model
{
    use SoftDeletes;

    protected $table = 'inscricoes';

    protected $fillable = [
        'evento_id',
        'participante_id',
        'codigo_qr',
        'data_inscricao',
    ];

    // Relacionamento com o Evento
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    // Relacionamento com o Usuário (Participante)[cite: 1]
    public function participante()
    {
        return $this->belongsTo(User::class, 'participante_id');
    }

    public function presenca(): HasOne
    {
        return $this->hasOne(Presenca::class, 'inscricao_id');
    }
    public function user()
    {
        // Vincula ao modelo User usando a tua chave estrangeira 'participante_id'
        return $this->belongsTo(User::class, 'participante_id');
    }
}
