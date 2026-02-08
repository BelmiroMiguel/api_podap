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
        Schema::create('tb_armazem', function (Blueprint $table) {
            $table->bigIncrements('idArmazem');
            $table->unsignedBigInteger('idEsquadra');
            $table->string('descricaoSetor');
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('idEsquadra')->references('idEsquadra')->on('tb_esquadra');
        });

        DB::table('tb_armazem')->insert([
            'idEsquadra' => 1,
            'descricaoSetor' => 'Setor de Achados - Prateleira 01',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_armazem');
    }
};
