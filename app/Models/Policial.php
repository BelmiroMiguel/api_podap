<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Policial extends Authenticatable
{
    protected $table = 'tb_policial';
    protected $primaryKey = 'idPolicial';

    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'idRole',
        'idEsquadra',
        'nip',
        'patente',
        'dataCadastro'
    ];

    // Relacionamento com o Role padrão do policial
    public function role()
    {
        return $this->belongsTo(Role::class, 'idRole', 'idRole');
    }

    // esquadra do policial
    public function esquadra()
    {
        return $this->belongsTo(Esquadra::class, 'idEsquadra', 'idEsquadra');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    // O "Killer Feature": Permissões específicas/sobrescritas
    public function permissoesCustomizadas()
    {
        return $this->belongsToMany(Permissao::class, 'tb_policial_permissao', 'idPolicial', 'idPermissao')
            ->withPivot('permitido');
    }

    /**
     * Lógica Principal: Verifica se o policial tem permissão
     * 1. Verifica se existe uma sobrescrita (tb_policial_permissao)
     * 2. Se não houver sobrescrita, verifica o Role (tb_role_permissao)
     */
    public function temPermissao(string $permissaoValue): bool
    {
        // 1. Verificar sobrescrita individual
        $custom = $this->permissoesCustomizadas()
            ->where('value', $permissaoValue)
            ->first();

        if ($custom) {
            return (bool) $custom->pivot->permitido;
        }

        // 2. Se não tem sobrescrita, checa a Role
        if (!$this->role) {
            return false;
        }

        return $this->role->permissoes()
            ->where('value', $permissaoValue)
            ->exists();
    }
}
