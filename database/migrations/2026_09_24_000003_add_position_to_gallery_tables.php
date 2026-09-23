<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FE gallery items support object-position (e.g. "center 62%").
     */
    public function up(): void
    {
        Schema::table('project_gallery', function (Blueprint $table) {
            $table->string('position', 255)->nullable()->after('alt');
        });

        Schema::table('partner_gallery', function (Blueprint $table) {
            $table->string('position', 255)->nullable()->after('alt');
        });
    }

    public function down(): void
    {
        Schema::table('project_gallery', function (Blueprint $table) {
            $table->dropColumn(['position']);
        });

        Schema::table('partner_gallery', function (Blueprint $table) {
            $table->dropColumn(['position']);
        });
    }
};
