<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable; // Importante para login
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'tb_usuario';
    protected $primaryKey = 'idUsuario';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'identificacao',
        'tipoIdentificacao',
        'telefone',
        'email',
        'senha',
        'tipoUsuario',
        'descEndereco',
        'foto'
    ];

    protected $hidden = ['senha']; // Não mostrar a senha em JSONs
    protected $appends = ['fotoUrl'];

    public function getAuthPassword()
    {
        return $this->senha;
    }

    // Relacionamento com Policial (se for um)
    public function policial()
    {
        return $this->hasOne(Policial::class, 'idUsuario', 'idUsuario');
    }

    public function isPolicial(): bool
    {
        return $this->tipoUsuario === 'POLICIAL';
    }

    public function getIdEsquadraAttribute(): ?int
    {
        return $this->policial ? $this->policial->idEsquadra : null;
    }


    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return asset('images/default-img.png'); // Ou uma imagem padrão: url("api/usuario/foto/default.png")
        }

        // Retorna exatamente o formato que você pediu
        return url("api/usuario/foto/" . basename($this->foto));
    }
}
