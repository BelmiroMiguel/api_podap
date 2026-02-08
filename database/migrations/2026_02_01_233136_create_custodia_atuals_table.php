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
        Schema::create('tb_custodia_atual', function (Blueprint $table) {
            $table->bigIncrements('idCustodiaAtual');
            $table->unsignedBigInteger('idOcorrencia');
            $table->enum('tipoDetentor', ['CIDADAO', 'ESQUADRA']);
            $table->unsignedBigInteger('idDetentor');
            $table->unsignedBigInteger('idArmazem')->nullable();
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('idOcorrencia')->references('idOcorrencia')->on('tb_ocorrencia');
            $table->foreign('idArmazem')->references('idArmazem')->on('tb_armazem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_custodia_atual');
    }
};
