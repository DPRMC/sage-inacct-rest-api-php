<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sage_intacct_report_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('report_identifier', 255);
            $table->string('report_name', 255)->nullable();
            $table->string('status', 32);
            $table->dateTime('requested_at');
            $table->dateTime('completed_at')->nullable();
            $table->char('request_checksum', 64);
            $table->char('response_checksum', 64)->nullable();
            $table->json('request_payload');
            $table->string('parser_version', 100);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['report_identifier', 'requested_at']);
            $table->index(['status', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sage_intacct_report_runs');
    }
};
