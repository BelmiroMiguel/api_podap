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
        Schema::create('tb_historico_movimentacao', function (Blueprint $table) {
            $table->bigIncrements('idHistoricoMovimentacao');
            $table->unsignedBigInteger('idOcorrencia');
            $table->string('origemDescricao');
            $table->string('destinoDescricao');
            $table->string('descricao');
            $table->unsignedBigInteger('idPolicialIntermediario')->nullable();
            $table->dateTime('dataMovimentacao')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('idOcorrencia')->references('idOcorrencia')->on('tb_ocorrencia');
            $table->foreign('idPolicialIntermediario')->references('idPolicial')->on('tb_policial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_historico_movimentacao');
    }
};
