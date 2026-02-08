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
        Schema::create('tb_categoria', function (Blueprint $table) {
            $table->bigIncrements('idCategoria');
            $table->string('descricao');
            $table->boolean('eliminado')->default(false);
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::table('tb_categoria')->insert([
            ['descricao' => 'Documentos'],
            ['descricao' => 'Dispositivos Eletrónicos'],
            ['descricao' => 'Joias e Relógios'],
            ['descricao' => 'Outros'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_categoria');
    }
};
