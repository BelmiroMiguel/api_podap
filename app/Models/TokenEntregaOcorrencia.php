<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenEntregaOcorrencia extends Model
{
    protected $table = 'tb_token_entrega_ocorrencia';

    protected $primaryKey = 'idTokenntrEgaOcorrencia';

    // Como na migration não definiste $table->timestamps(), desativamos aqui
    public $timestamps = false;

    protected $fillable = [
        'idOcorrencia',
        'token',
        'dataExpiracao',
        'idUsuarioRecebedor',
    ];


    protected $casts = [
        'dataExpiracao' => 'datetime',
    ];


    /**
     * Relacionamento: O token pertence a uma ocorrência.
     */
    public function ocorrencia()
    {
        return $this->belongsTo(Ocorrencia::class, 'idOcorrencia', 'idOcorrencia');
    }
}
