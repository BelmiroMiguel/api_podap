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
        // tb_role - Grupos (ADMIN, OPERADOR, AGENTE_CAMPO)
        Schema::create('tb_role', function (Blueprint $table) {
            $table->bigIncrements('idRole');
            $table->string('nome'); // ex: 'Administrador de Esquadra'
            $table->string('descricao')->nullable();
            $table->unsignedBigInteger('idEsquadra');
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('idEsquadra')->references('idEsquadra')->on('tb_esquadra');
        });

        DB::table('tb_role')->insert([
            'nome' => 'ADMINISTRADOR',
            'descricao' => 'Role com todas as permissões para gerir a esquadra',
            'idEsquadra' => 1,
        ]);

        DB::table('tb_role')->insert([
            'nome' => 'OPERADOR',
            'descricao' => 'Role com permissões para registar e gerir itens na esquadra',
            'idEsquadra' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_role');
    }
};
