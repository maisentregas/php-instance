<?php

namespace App\Console\Commands;

use App\Services\RaiaDrogasilService;
use Illuminate\Console\Command;

class RaiaDrogasilSchedulerCommand extends Command
{
    private $raiaDrogasilService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:raia-drogasil-scheduler-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Google Pub/Sub listener to RaiaDrogasil messages.';

    public function __construct(RaiaDrogasilService $raiaDrogasilService)
    {
        parent::__construct();
        $this->raiaDrogasilService = $raiaDrogasilService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->raiaDrogasilService->handleMessages();
    }
}
