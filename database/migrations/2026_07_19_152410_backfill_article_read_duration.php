<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('articles')
            ->select(['id', 'content'])
            ->orderBy('id')
            ->get()
            ->each(function (object $article): void {
                $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $article->content)) ?? '');
                preg_match_all('/[\p{L}\p{N}]+/u', $plainText, $matches);

                DB::table('articles')
                    ->where('id', $article->id)
                    ->update([
                        'read_duration' => max(1, (int) ceil(count($matches[0]) / 200)),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This data-only migration has no reversible changes.
    }
};
