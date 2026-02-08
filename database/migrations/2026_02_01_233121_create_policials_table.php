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
        Schema::create('tb_policial', function (Blueprint $table) {
            $table->bigIncrements('idPolicial');
            $table->unsignedBigInteger('idRole');
            $table->unsignedBigInteger('idUsuario');
            $table->unsignedBigInteger('idEsquadra');
            $table->string('nip')->unique();
            $table->string('patente');
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('idUsuario')->references('idUsuario')->on('tb_usuario');
            $table->foreign('idEsquadra')->references('idEsquadra')->on('tb_esquadra');
            $table->foreign('idRole')->references('idRole')->on('tb_role');
        });

        DB::table('tb_policial')->insert([
            'idUsuario' => 1,
            'idEsquadra' => 1,
            'nip' => 'PN2024100',
            'patente' => 'Superintendente',
            'idRole' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_policial');
    }
};
