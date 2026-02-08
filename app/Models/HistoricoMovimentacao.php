<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoMovimentacao extends Model
{
    protected $table = 'tb_historico_movimentacao';
    protected $primaryKey = 'idHistoricoMovimentacao';
    public $timestamps = false;

    protected $fillable = [
        'idOcorrencia',
        'origemDescricao',
        'destinoDescricao',
        'descricao',
        'idPolicialIntermediario',
        'dataMovimentacao'
    ];

    public function ocorrencia()
    {
        return $this->belongsTo(Ocorrencia::class, 'idOcorrencia', 'idOcorrencia');
    }

    public function policial()
    {
        return $this->belongsTo(Policial::class, 'idPolicialIntermediario', 'idPolicial');
    }
}
