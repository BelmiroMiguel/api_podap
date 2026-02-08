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
        Schema::create('tb_usuario', function (Blueprint $table) {
            $table->bigIncrements('idUsuario');
            $table->string('nome');
            $table->string('identificacao')->unique();
            $table->enum('tipoIdentificacao', ['BI', 'PASSAPORTE'])->default('BI');
            $table->string('telefone');
            $table->string('email')->unique();
            $table->string('senha');
            $table->string('foto')->nullable();
            $table->enum('tipoUsuario', ['CIDADAO', 'POLICIAL']);
            $table->dateTime('dataCadastro')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        DB::table('tb_usuario')->insert([
            'nome' => 'Admin Sistema',
            'identificacao' => '000000000LA000',
            'telefone' => '900000000',
            'email' => '2b.belmiro@gmail.com',
            'senha' => bcrypt('Aa@123456'),
            'tipoUsuario' => 'POLICIAL',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_usuario');
    }


    // quero adincionar foto de perfil $table->string('foto')->nullable()->after('email');


};
