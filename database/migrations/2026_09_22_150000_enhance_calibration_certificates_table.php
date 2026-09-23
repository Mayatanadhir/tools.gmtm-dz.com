<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibration_certificates', function (Blueprint $table): void {
            $table->string('certificate_type', 50)->default('periodic')->after('reference')->comment('Certificate type: initial, periodic, after_repair, intermediate');
            $table->date('expiry_date')->nullable()->index()->after('calibration_date')->comment('Calculated or explicit certificate expiry date');
            $table->unsignedSmallInteger('validity_period_months')->default(12)->after('expiry_date')->comment('Validity duration in months');
            $table->string('status', 50)->default('draft')->index()->after('is_locked')->comment('Lifecycle status enum');
            $table->string('certificate_path', 255)->nullable()->after('status')->comment('Relative path to optimized PDF file');
            $table->string('certificate_hash', 64)->nullable()->index()->after('certificate_path')->comment('SHA256 hash for CAS deduplication');
            $table->string('file_name', 255)->nullable()->after('certificate_hash')->comment('Original uploaded file name');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_name')->comment('File size in bytes');
            $table->string('mime_type', 100)->nullable()->after('file_size')->comment('MIME content type');
            $table->json('environmental_conditions')->nullable()->after('mime_type')->comment('Ambient conditions: temp, humidity, pressure');
            $table->text('remarks')->nullable()->after('environmental_conditions')->comment('Metrologist technical remarks');
            $table->foreignId('previous_certificate_id')->nullable()->after('remarks')->constrained('calibration_certificates')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('previous_certificate_id')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('locked_at')->nullable()->after('approved_at');
            $table->foreignId('locked_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('calibration_certificates', function (Blueprint $table): void {
            $table->dropForeign(['previous_certificate_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['locked_by']);

            $table->dropColumn([
                'certificate_type',
                'expiry_date',
                'validity_period_months',
                'status',
                'certificate_path',
                'certificate_hash',
                'file_name',
                'file_size',
                'mime_type',
                'environmental_conditions',
                'remarks',
                'previous_certificate_id',
                'created_by',
                'updated_by',
                'approved_by',
                'approved_at',
                'locked_at',
                'locked_by',
            ]);
        });
    }
};
