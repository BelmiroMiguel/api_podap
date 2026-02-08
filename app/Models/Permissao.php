<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    protected $table = 'tb_permissao';
    protected $primaryKey = 'idPermissao';

    // Desativando timestamps padrão do Laravel pois você usa dataCadastro
    public $timestamps = false;

    protected $fillable = ['value', 'descricao', 'dataCadastro'];

    // Relacionamento com Roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'tb_role_permissao', 'idPermissao', 'idRole');
    }

    // Relacionamento com Policiais (através da tabela de sobrescrita)
    public function policiais()
    {
        return $this->belongsToMany(Policial::class, 'tb_policial_permissao', 'idPermissao', 'idPolicial')
                    ->withPivot('permitido');
    }
}
