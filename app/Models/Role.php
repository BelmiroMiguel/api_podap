<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'tb_role';
    protected $primaryKey = 'idRole';
    public $timestamps = false;

    protected $fillable = ['nome', 'descricao', 'idEsquadra', 'dataCadastro'];

    public function esquadra()
    {
        return $this->belongsTo(Esquadra::class, 'idEsquadra', 'idEsquadra');
    }
    
    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'tb_role_permissao', 'idRole', 'idPermissao')
            ->using(RolePermissao::class) // Indica que deve usar o Model acima
            ->withPivot('idRolePermissao');
    }


    // Relacionamento com Policiais (Assumindo que tb_policial tem idRole ou existe uma pivot)
    // Se um policial tiver apenas UM role:
    public function policiais()
    {
        return $this->hasMany(Policial::class, 'idRole', 'idRole');
    }
}
