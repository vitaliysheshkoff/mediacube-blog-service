<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['published_at'], 'posts_published_at_idx');
        });

        DB::statement('
            CREATE INDEX posts_title_trgm_idx
            ON posts USING gin (title gin_trgm_ops)
        ');

        DB::statement('
            CREATE INDEX posts_body_trgm_idx
            ON posts USING gin (body gin_trgm_ops)
        ');

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['post_id', 'created_at'], 'comments_post_id_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_published_at_idx');
        });

        DB::statement('DROP INDEX IF EXISTS posts_title_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS posts_body_trgm_idx');

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_post_id_created_at_idx');
        });
    }
};
