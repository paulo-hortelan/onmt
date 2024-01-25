<?php

namespace PauloHortelan\Onmt\Commands;

use Illuminate\Console\Command;

class OnmtCommand extends Command
{
    public $signature = 'onmt';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
