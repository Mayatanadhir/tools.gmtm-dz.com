<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\CalibrationCertificateStatus;
use App\Models\CalibrationCertificate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringCertificatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrology:check-expiring-certificates {--days=30 : Expiry warning threshold in days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate calibration certificate expiration status and update ISO lifecycle states.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $warningDays = (int) $this->option('days');
        $warningDate = Carbon::today()->addDays($warningDays);

        $this->info("Scanning calibration certificates against today [{$today->toDateString()}]...");

        // 1. Transition past-due certificates to Expired
        $expiredCount = CalibrationCertificate::whereIn('status', [
            CalibrationCertificateStatus::Approved,
            CalibrationCertificateStatus::ExpiringSoon,
        ])
            ->whereDate('expiry_date', '<', $today)
            ->update([
                'status' => CalibrationCertificateStatus::Expired,
            ]);

        $this->info("Transitioned [{$expiredCount}] certificates to [Expired].");

        // 2. Transition certificates within warning window to ExpiringSoon
        $expiringCount = CalibrationCertificate::where('status', CalibrationCertificateStatus::Approved)
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $warningDate)
            ->update([
                'status' => CalibrationCertificateStatus::ExpiringSoon,
            ]);

        $this->info("Transitioned [{$expiringCount}] certificates to [ExpiringSoon].");

        $this->info('Calibration certificate status check completed successfully.');

        return Command::SUCCESS;
    }
}
