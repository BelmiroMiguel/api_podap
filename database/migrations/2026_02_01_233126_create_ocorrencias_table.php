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
        Schema::create('tb_ocorrencia', function (Blueprint $table) {
            $table->bigIncrements('idOcorrencia');
            $table->unsignedBigInteger('idItem');
            $table->unsignedBigInteger('idUsuario');
            $table->enum('tipoOcorrencia', ['PERDIDO', 'ACHADO']);
            $table->enum('statusProcesso', ['PROCURANDO', 'AGUARDANDO_CONFIRMACAO', 'ENTREGUE', 'NEGADO',])->default('PROCURANDO');
            $table->dateTime('dataEvento');
            $table->string('localEvento');
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->boolean('eliminado')->default(false);

            $table->foreign('idItem')->references('idItem')->on('tb_item');
            $table->foreign('idUsuario')->references('idUsuario')->on('tb_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ocorrencia');
    }
};
