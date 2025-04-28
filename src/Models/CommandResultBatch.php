<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CommandResultBatch extends Model
{
    protected $table = 'command_result_batches';

    const UPDATED_AT = null;

    protected $fillable = [
        'ip',
        'description',
        'pon_interface',
        'interface',
        'serial',
        'operator',
        'created_at',
        'finished_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function commands(): HasMany
    {
        return $this->hasMany(CommandResult::class, 'batch_id');
    }

    /**
     * Get the first command with error
     */
    public function firstError()
    {
        return $this->commands()->where('success', false)->first();
    }

    /**
     * Check if all commands in the batch were successful.
     */
    public function allCommandsSuccessful(): bool
    {
        return $this->commands()->where('success', false)->doesntExist();
    }

    /**
     * Check if the last command in the batch was successful.
     */
    public function wasLastCommandSuccessful(): bool
    {
        $lastCommand = $this->commands()->orderBy('id', 'desc')->first();

        return $lastCommand ? $lastCommand->success : false;
    }

    /**
     * Associate commands with this batch.
     */
    public function associateCommand(CommandResult $commandResult)
    {
        $commandResult->associateBatch($this);

        $this->load('commands');
    }

    /**
     * Associate commands with this batch.
     */
    public function associateCommands(Collection $commandResults)
    {
        foreach ($commandResults as $commandResult) {
            $commandResult->associateBatch($this);
        }

        $this->load('commands');
    }

    /**
     * Calculate the execution time in seconds.
     * Returns null if finished_at is not set.
     */
    public function executionTimeInSeconds(): ?int
    {
        if ($this->finished_at && $this->created_at) {
            return $this->finished_at->diffInSeconds($this->created_at);
        }

        return null;
    }
}
