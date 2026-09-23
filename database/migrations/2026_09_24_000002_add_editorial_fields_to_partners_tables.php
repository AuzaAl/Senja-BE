<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editorial fields required by FE PartnerDetail / PartnerDirectory
     * and CMS partner-form.
     */
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('number', 10)->nullable()->after('slug');
            $table->string('category', 255)->nullable()->after('name');
            $table->json('capabilities')->nullable()->after('description');
            $table->text('relationship')->nullable()->after('capabilities');
            $table->text('relationship_detail')->nullable()->after('relationship');
            $table->string('hero_image', 2048)->nullable()->after('logo');
        });

        Schema::table('partner_products', function (Blueprint $table) {
            $table->string('category', 255)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('partner_products', function (Blueprint $table) {
            $table->dropColumn(['category']);
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'number',
                'category',
                'capabilities',
                'relationship',
                'relationship_detail',
                'hero_image',
            ]);
        });
    }
};
