<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
                $table->enum('type', ['trial', 'paid'])->default('trial');
                $table->enum('status', ['active', 'cancelled', 'expired'])->default('active');
                $table->timestamp('trial_end_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('plan_id');
            });
        } else {
            Schema::table('subscriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('subscriptions', 'type')) {
                    $table->enum('type', ['trial', 'paid'])->default('trial')->after('plan_id');
                }
                if (!Schema::hasColumn('subscriptions', 'status')) {
                    $table->enum('status', ['active', 'cancelled', 'expired'])->default('active')->after('type');
                }
                if (!Schema::hasColumn('subscriptions', 'trial_end_at')) {
                    $table->timestamp('trial_end_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('subscriptions', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('trial_end_at');
                }
                if (!Schema::hasColumn('subscriptions', 'ended_at')) {
                    $table->timestamp('ended_at')->nullable()->after('started_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
