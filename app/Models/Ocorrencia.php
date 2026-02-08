<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ocorrencia extends Model
{
    protected $table = 'tb_ocorrencia';

    protected $primaryKey = 'idOcorrencia';

    public $timestamps = false;

    protected $fillable = [
        'idItem',
        'idUsuario',
        'tipoOcorrencia', // PERDIDO ou ACHADO
        'statusProcesso', // PROCURANDO, AGUARDANDO_CONFIRMACAO, ENTREGUE
        'dataEvento',
        'localEvento',
        'eliminado',
        'dataCadastro'
    ];

    protected $casts = [
        'dataEvento' => 'datetime',
        'dataCadastro' => 'datetime',
    ];


    // Relacionamentos
    public function item()
    {
        return $this->belongsTo(Item::class, 'idItem', 'idItem');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    public function custodia()
    {
        return $this->hasOne(CustodiaAtual::class, 'idOcorrencia', 'idOcorrencia');
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoMovimentacao::class, 'idOcorrencia', 'idOcorrencia');
    }

    public function token()
    {
        return $this->hasOne(TokenEntregaOcorrencia::class, 'idOcorrencia', 'idOcorrencia');
    }

    public function temTokenValido(): bool
    {
        return $this->token && $this->token->dataExpiracao->isFuture();
    }
}
