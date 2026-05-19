<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['class_id', 'user_id']);
        });

        if (Schema::hasColumn('school_classes', 'teacher_id')) {
            foreach (DB::table('school_classes')->whereNotNull('teacher_id')->get() as $class) {
                DB::table('class_teacher')->insertOrIgnore([
                    'class_id'   => $class->id,
                    'user_id'    => $class->teacher_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('school_classes', function (Blueprint $table) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
        });

        $assignments = DB::table('class_teacher')
            ->select('class_id', DB::raw('MIN(user_id) as user_id'))
            ->groupBy('class_id')
            ->get();

        foreach ($assignments as $row) {
            DB::table('school_classes')
                ->where('id', $row->class_id)
                ->update(['teacher_id' => $row->user_id]);
        }

        Schema::dropIfExists('class_teacher');
    }
};
