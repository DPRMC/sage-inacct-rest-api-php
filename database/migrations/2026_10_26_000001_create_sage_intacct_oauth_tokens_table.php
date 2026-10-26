<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sage_intacct_oauth_tokens', function (Blueprint $table): void {
            $table->id();
            $table->string('connection_name', 100)->unique();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->dateTime('expires_at');
            $table->text('scope')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sage_intacct_oauth_tokens');
    }
};
