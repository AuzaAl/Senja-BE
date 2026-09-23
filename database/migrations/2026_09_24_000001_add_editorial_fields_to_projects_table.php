<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editorial fields required by FE ProjectDetail / WorkArchive
     * and CMS project-form. All nullable for backward compatibility.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('number', 10)->nullable()->after('slug');
            $table->string('location', 255)->nullable()->after('category');
            $table->string('year', 10)->nullable()->after('location');
            $table->string('client', 255)->nullable()->after('year');
            $table->string('description', 2000)->nullable()->after('summary');
            $table->longText('overview')->nullable()->after('description');
            $table->longText('challenge')->nullable()->after('overview');
            $table->longText('solution')->nullable()->after('challenge');
            $table->json('services')->nullable()->after('solution');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'number',
                'location',
                'year',
                'client',
                'description',
                'overview',
                'challenge',
                'solution',
                'services',
            ]);
        });
    }
};
