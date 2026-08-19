<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->string('status')->default('confirmada')->after('codigo_qr'); // pendente | confirmada | rejeitada
            $table->string('comprovativo')->nullable()->after('status');
            $table->text('observacao_avaliacao')->nullable()->after('comprovativo');
            $table->foreignId('avaliado_por')->nullable()->constrained('users')->nullOnDelete()->after('observacao_avaliacao');
            $table->timestamp('avaliado_em')->nullable()->after('avaliado_por');
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('avaliado_por');
            $table->dropColumn(['status', 'comprovativo', 'observacao_avaliacao', 'avaliado_em']);
        });
    }
};