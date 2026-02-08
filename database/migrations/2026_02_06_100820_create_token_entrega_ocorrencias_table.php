<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_token_entrega_ocorrencia', function (Blueprint $table) {
            $table->bigIncrements('idTokenntrEgaOcorrencia');
            $table->unsignedBigInteger('idOcorrencia')->unique(); // Um token por ocorrência
            $table->unsignedBigInteger('idUsuarioRecebedor');

            $table->string('token'); // O hash ou código aleatório
            $table->timestamp('dataExpiracao'); // validade do token
            $table->foreign('idOcorrencia')->references('idOcorrencia')->on('tb_ocorrencia')->onDelete('cascade');
            $table->foreign('idUsuarioRecebedor')->references('idUsuario')->on('tb_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_token_entrega_ocorrencia');
    }
};
