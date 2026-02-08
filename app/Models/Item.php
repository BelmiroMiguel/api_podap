<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Item extends Model
{
    protected $table = 'tb_item';
    protected $primaryKey = 'idItem';
    public $timestamps = false;

    protected $fillable = [
        'idCategoria',
        'titulo',
        'descricao',
        'detalhe',
        'fotosItem',
        'dataCadastro'
    ];

    /**
     * Casts para transformar JSON do banco em Array do PHP automaticamente
     */
    protected $casts = [
        'detalhe' => 'array',
        'fotosItem' => 'array',
    ];

    protected $appends = ['fotosItemUrl'];

    // No seu Item.php corrija para:
    public function getFotosItemUrlAttribute()
    {
        // Verifique se o array fotosItem está vazio ou nulo
        if (empty($this->fotosItem)) {
            return [asset('images/default-img.png')];
        }

        return array_map(function ($foto) {
            return url("api/ocorrencia/foto/" . basename($foto));
        }, $this->fotosItem);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function ocorrencia()
    {
        return $this->hasOne(Ocorrencia::class, 'idItem', 'idItem');
    }
}
