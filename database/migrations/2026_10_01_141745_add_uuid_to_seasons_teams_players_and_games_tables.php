<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->uuid('uuid')->nullable()->after('id');
            });

            $ids = DB::table($tableName)->orderBy('id')->pluck('id');

            foreach ($ids as $id) {
                DB::table($tableName)->where('id', $id)->update([
                    'uuid' => (string) Str::uuid7(),
                ]);
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->uuid('uuid')->nullable(false)->change();
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        return ['seasons', 'teams', 'players', 'games'];
    }
};
