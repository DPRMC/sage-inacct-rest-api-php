<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sage_intacct_report_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('run_id')
                ->constrained('sage_intacct_report_runs')
                ->cascadeOnDelete();
            $table->string('report_identifier', 255);
            $table->string('source_key', 255);
            $table->char('source_checksum', 64);
            $table->json('source_payload');
            $table->json('normalized_payload')->nullable();
            $table->timestamps();

            $table->unique(['report_identifier', 'source_key']);
            $table->index(['run_id', 'source_checksum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sage_intacct_report_records');
    }
};
