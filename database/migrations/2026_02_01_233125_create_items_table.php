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
        Schema::create('tb_item', function (Blueprint $table) {
            $table->bigIncrements('idItem');
            $table->unsignedBigInteger('idCategoria');
            $table->string('titulo');
            $table->text('descricao');
            $table->json('detalhe')->nullable();
            $table->json('fotosItem')->nullable();
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('idCategoria')->references('idCategoria')->on('tb_categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_item');
    }
};
