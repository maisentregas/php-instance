<?php

use App\Console\Commands\RaiaDrogasilSchedulerCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RaiaDrogasilSchedulerCommand::class)->everyMinute();
