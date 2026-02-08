<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermissao extends Pivot
{
    protected $table = 'tb_role_permissao';

    // Como você definiu uma PK customizada na migration, informamos aqui:
    protected $primaryKey = 'idRolePermissao';

    public $timestamps = false;

    protected $fillable = [
        'idRole',
        'idPermissao'
    ];

    // Relacionamentos inversos (caso precise acessar o objeto a partir da pivot)
    public function role()
    {
        return $this->belongsTo(Role::class, 'idRole', 'idRole');
    }

    public function permissao()
    {
        return $this->belongsTo(Permissao::class, 'idPermissao', 'idPermissao');
    }
}
