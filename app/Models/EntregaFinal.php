<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntregaFinal extends Model
{
    protected $table = 'tb_entrega_final';
    protected $primaryKey = 'idEntregaFinal';
    public $timestamps = false;

    protected $fillable = [
        'idOcorrencia',
        'idUsuarioEntregador',
        'idUsuarioRecebedor',
        'tokenConfirmacao',
        'descricaoEntrega',
        'fotosEntrega',
        'dataEntrega'
    ];

    // Cast para o array de fotos da entrega
    protected $casts = [
        'fotosEntrega' => 'array',
        'dataEntrega' => 'datetime'
    ];

    protected $appends = ['fotosEntregaUrl'];

    public function ocorrencia()
    {
        return $this->belongsTo(Ocorrencia::class, 'idOcorrencia', 'idOcorrencia');
    }

    public function entregador()
    {
        return $this->belongsTo(Usuario::class, 'idUsuarioEntregador', 'idUsuario');
    }

    public function recebedor()
    {
        return $this->belongsTo(Usuario::class, 'idUsuarioRecebedor', 'idUsuario');
    }

    public function getFotosEntregaUrlAttribute()
    {
        if (!$this->foto) {
            return [];
        }

        return array_map(function ($foto) {
            return url("api/entrega-ocorrencia/foto/" . basename($foto));
        }, $this->fotosEntrega);
    }
}
