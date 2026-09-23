<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedInteger('pessoa_id_stw')->nullable()->after('tokenable_id')->index();
            $table->foreign('pessoa_id_stw', 'personal_access_tokens_pessoa_fk')
                ->references('pessoa_id_stw')
                ->on('pessoas');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropForeign('personal_access_tokens_pessoa_fk');
            $table->dropColumn('pessoa_id_stw');
        });
    }
};
