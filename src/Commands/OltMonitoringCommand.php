<?php

namespace PauloHortelan\OltMonitoring\Commands;

use Illuminate\Console\Command;

class OltMonitoringCommand extends Command
{
    public $signature = 'olt-monitoring';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
