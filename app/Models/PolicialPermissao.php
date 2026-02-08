<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PolicialPermissao extends Pivot
{
    protected $table = 'tb_policial_permissao';
    protected $primaryKey = 'idPolicialPermissao';
    public $timestamps = false;

    protected $fillable = [
        'idPolicial',
        'idPermissao',
        'permitido'
    ];
}
