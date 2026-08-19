<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->string('comprovativo_hash', 64)->nullable()->index()->after('comprovativo');
            $table->string('banco')->nullable()->after('comprovativo_hash');
            $table->string('referencia_pagamento')->nullable()->after('banco');
            $table->decimal('valor_pago', 10, 2)->nullable()->after('referencia_pagamento');
            $table->date('data_pagamento')->nullable()->after('valor_pago');
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropColumn(['comprovativo_hash', 'banco', 'referencia_pagamento', 'valor_pago', 'data_pagamento']);
        });
    }
};