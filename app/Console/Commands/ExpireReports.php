<?php
// app/Console/Commands/ExpireReports.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;

class ExpireReports extends Command
{
    protected $signature = 'reports:expire';
    protected $description = 'Expire reports whose expires_at has passed';

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle()
    {
        $this->info('Starting report expiration process...');

        $count = $this->reportService->expireReports();

        $this->info("Successfully processed {$count} expired reports.");

        return Command::SUCCESS;
    }
}
