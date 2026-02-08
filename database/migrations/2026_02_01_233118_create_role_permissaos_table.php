<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // tb_role_permissao - Ligação Padrão que um role pode fazer
        Schema::create('tb_role_permissao', function (Blueprint $table) {
            $table->bigIncrements('idRolePermissao');
            $table->unsignedBigInteger('idRole');
            $table->unsignedBigInteger('idPermissao');
            $table->foreign('idRole')->references('idRole')->on('tb_role');
            $table->foreign('idPermissao')->references('idPermissao')->on('tb_permissao');
        });

        // Associar permissões ao role ADMINISTRADOR
        $adminRoleId = DB::table('tb_role')->where('nome', 'ADMINISTRADOR')->value('idRole');
        $allPermissoes = DB::table('tb_permissao')->pluck('idPermissao');
        foreach ($allPermissoes as $permissaoId) {
            DB::table('tb_role_permissao')->insert([
                'idRole' => $adminRoleId,
                'idPermissao' => $permissaoId,
            ]);
        }

        // Associar permissões ao role OPERADOR
        $operadorRoleId = DB::table('tb_role')->where('nome', 'OPERADOR')->value('idRole');
        $operadorPermissoes = DB::table('tb_permissao')->whereIn('value', [
            'cadastrar_item',
            'editar_item',
            'transferir_custodia',
            'validar_entrega',
            'ver_relatorios_esquadra',
        ])->pluck('idPermissao');
        foreach ($operadorPermissoes as $permissaoId) {
            DB::table('tb_role_permissao')->insert([
                'idRole' => $operadorRoleId,
                'idPermissao' => $permissaoId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_role_permissao');
    }
};
