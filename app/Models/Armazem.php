<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Armazem extends Model
{
    protected $table = 'tb_armazem';
    protected $primaryKey = 'idArmazem';
    public $timestamps = false;

    protected $fillable = ['idEsquadra', 'descricaoSetor', 'dataCadastro'];

    public function esquadra()
    {
        return $this->belongsTo(Esquadra::class, 'idEsquadra', 'idEsquadra');
    }
}
