<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Esquadra extends Model
{
    protected $table = 'tb_esquadra';
    protected $primaryKey = 'idEsquadra';
    public $timestamps = false;

    protected $fillable = [
        'nome', 'provincia', 'municipio', 'endereco', 'telefone'
    ];

    // Relacionamentos
    public function policiais() {
        return $this->hasMany(Policial::class, 'idEsquadra', 'idEsquadra');
    }

    public function armazens() {
        return $this->hasMany(Armazem::class, 'idEsquadra', 'idEsquadra');
    }
}
