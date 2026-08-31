<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A payment schedule may carry at most one penalty - PaymentSchedule
     * declares hasOne(Penalty::class), and InspectForPenalty relies on
     * whereDoesntHave('penalty') to avoid double-charging. That guard is not
     * atomic: two overlapping runs of the command (the 01:00 cron plus a manual
     * run) can both pass it and both insert. Enforce it in the database.
     */
    public function up(): void
    {
        $duplicates = DB::table('penalties')
            ->select('payment_schedule_id', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_schedule_id')
            ->having('total', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Cannot add unique index: ' . $duplicates->count() . ' payment schedule(s) already carry '
                . 'more than one penalty (payment_schedule_id: '
                . $duplicates->pluck('payment_schedule_id')->implode(', ') . '). '
                . 'Resolve these duplicates before running this migration.'
            );
        }

        Schema::table('penalties', function (Blueprint $table) {
            $table->unique('payment_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropUnique(['payment_schedule_id']);
        });
    }
};
