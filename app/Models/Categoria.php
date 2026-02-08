<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'tb_categoria';
    protected $primaryKey = 'idCategoria';
    public $timestamps = false;

    protected $fillable = ['descricao', 'eliminado'];

    public function itens() {
        return $this->hasMany(Item::class, 'idCategoria', 'idCategoria');
    }
}
