<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('id_type');
            $table->string('id_document_path');
            $table->string('selfie_path');
            $table->enum('status', ['pending', 'verified', 'rejected', 'resubmission_required'])->default('pending');
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_version')->nullable();
            $table->timestamp('age_attested_at')->nullable();
            $table->timestamp('files_purge_after')->nullable();
            $table->timestamp('files_purged_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'id']);
            $table->index(['status', 'submitted_at']);
            $table->index(['reviewed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_identity_verifications');
    }
};
