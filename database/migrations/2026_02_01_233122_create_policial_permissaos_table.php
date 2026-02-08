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
        // tb_usuario_permissao - O "Killer Feature" (Sobrescrita o que um usuario pode ou não fazer independente do seu role)
        Schema::create('tb_policial_permissao', function (Blueprint $table) {
            $table->bigIncrements('idPolicialPermissao');
            $table->unsignedBigInteger('idPolicial');
            $table->unsignedBigInteger('idPermissao');
            $table->boolean('permitido')->default(true); // true = concede, false = revoga
            $table->foreign('idPolicial')->references('idPolicial')->on('tb_policial');
            $table->foreign('idPermissao')->references('idPermissao')->on('tb_permissao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_policial_permissao');
    }
};
