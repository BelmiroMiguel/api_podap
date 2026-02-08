<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustodiaAtual extends Model
{
    protected $table = 'tb_custodia_atual';
    protected $primaryKey = 'idCustodiaAtual';
    public $timestamps = false;

    protected $fillable = [
        'idOcorrencia',
        'tipoDetentor', // CIDADAO ou ESQUADRA
        'idDetentor',   // ID do Usuario ou ID da Esquadra
        'idArmazem',    // Opcional (se estiver na esquadra)
        'dataCadastro'
    ];

    public function ocorrencia()
    {
        return $this->belongsTo(Ocorrencia::class, 'idOcorrencia', 'idOcorrencia');
    }

    public function armazem()
    {
        return $this->belongsTo(Armazem::class, 'idArmazem', 'idArmazem');
    }

    /**
     * Relacionamento Dinâmico para o Detentor
     * Se for CIDADAO, retorna o Model Usuario.
     * Se for ESQUADRA, retorna o Model Esquadra.
     */
    public function detentor()
    {
        if ($this->tipoDetentor === 'CIDADAO') {
            return $this->belongsTo(Usuario::class, 'idDetentor', 'idUsuario');
        }
        return $this->belongsTo(Esquadra::class, 'idDetentor', 'idEsquadra');
    }
}
