<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Model;

class CommandResultBatch extends Model
{
    protected $table = 'command_result_batches';

    public $timestamps = true;

    protected $fillable = [
        'interface',
        'serial',
        'commands',
    ];

    protected $casts = [
        'commands' => 'array',
    ];

    /**
     * Add a command result to the batch.
     *
     * @param  \PauloHortelan\Onmt\DTOs\CommandResult  $commandResult
     */
    public function addCommand($commandResult): void
    {
        $commands = $this->commands;
        $commands[] = $commandResult->toArray();

        $this->update(['commands' => $commands]);
    }
}
