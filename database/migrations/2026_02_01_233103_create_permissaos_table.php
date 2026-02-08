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
        // tb_permissao - Catálogo de Ações (permições)
        Schema::create('tb_permissao', function (Blueprint $table) {
            $table->bigIncrements('idPermissao');
            $table->string('value')->unique(); // ex: 'cadastrar_item'
            $table->string('descricao')->nullable();
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::table('tb_permissao')->insert([
            ['value' => 'criar_esquadra', 'descricao' => 'Pode criar novas esquadras'],
            ['value' => 'gerir_esquadras', 'descricao' => 'Pode gerir as esquadras'],
            ['value' => 'cadastrar_item', 'descricao' => 'Pode registrar itens achados/perdidos'],
            ['value' => 'editar_item', 'descricao' => 'Pode editar informações de itens'],
            ['value' => 'eliminar_item', 'descricao' => 'Pode apagar registros (Admin)'],
            ['value' => 'gerir_policiais', 'descricao' => 'Pode cadastrar/editar outros policiais'],
            ['value' => 'transferir_custodia', 'descricao' => 'Pode receber objetos de cidadãos'],
            ['value' => 'validar_entrega', 'descricao' => 'Pode marcar objeto como entregue ao dono'],
            ['value' => 'ver_relatorios_esquadra', 'descricao' => 'Pode ver estatísticas de objetos na unidade'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_permissao');
    }
};
