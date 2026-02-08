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
        Schema::create('tb_entrega_final', function (Blueprint $table) {
            $table->bigIncrements('idEntregaFinl');
            $table->unsignedBigInteger('idOcorrencia');
            $table->unsignedBigInteger('idUsuarioEntregador');
            $table->unsignedBigInteger('idUsuarioRecebedor');
            $table->string('tokenConfirmacao')->nullable();
            $table->string('descricaoEntrega')->nullable();
            $table->json('fotosEntrega')->nullable();
            $table->dateTime('dataEntrega')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('idOcorrencia')->references('idOcorrencia')->on('tb_ocorrencia');
            $table->foreign('idUsuarioEntregador')->references('idUsuario')->on('tb_usuario');
            $table->foreign('idUsuarioRecebedor')->references('idUsuario')->on('tb_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_entrega_final');
    }
};
