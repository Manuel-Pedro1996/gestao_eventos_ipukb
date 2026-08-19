<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->unique('comprovativo_hash', 'inscricoes_comprovativo_hash_unique');
            $table->unique(['banco', 'referencia_pagamento', 'data_pagamento'], 'inscricoes_pagamento_unico');
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropUnique('inscricoes_pagamento_unico');
            $table->dropUnique('inscricoes_comprovativo_hash_unique');
        });
    }
};