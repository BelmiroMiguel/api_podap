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
        Schema::create('tb_esquadra', function (Blueprint $table) {
            $table->bigIncrements('idEsquadra');
            $table->string('nome');
            $table->string('provincia');
            $table->string('municipio');
            $table->string('endereco');
            $table->string('telefone');
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::table('tb_esquadra')->insert([
            'nome' => 'Comando Geral - Esquadra A',
            'provincia' => 'Luanda',
            'municipio' => 'Luanda',
            'endereco' => 'Ilha de Luanda',
            'telefone' => '222000111',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_esquadra');
    }
};
