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

    /**
     * Flag to indicate if the batch is in in-memory mode (not persisted to database)
     */
    public $inMemoryMode = false;

    /**
     * In-memory commands collection
     */
    protected $inMemoryCommands = null;

    /**
     * When accessing the toArray or toJson output, make sure in-memory commands are included
     */
    public function toArray()
    {
        $array = parent::toArray();

        if ($this->inMemoryMode && $this->inMemoryCommands !== null) {
            $array['commands'] = $this->inMemoryCommands->toArray();
        }

        return $array;
    }

    /**
     * Get the in-memory commands
     */
    public function getInMemoryCommands(): ?Collection
    {
        return $this->inMemoryCommands;
    }

    /**
     * Relationship to command results
     */
    public function commands(): HasMany
    {
        return $this->hasMany(CommandResult::class, 'batch_id');
    }

    /**
     * Get command results - either from database or in-memory
     */
    public function getCommands(): Collection
    {
        if ($this->inMemoryMode && $this->inMemoryCommands !== null) {
            return $this->inMemoryCommands;
        }

        return $this->commands;
    }

    /**
     * Get the first command with error
     */
    public function firstError()
    {
        if ($this->inMemoryMode && $this->inMemoryCommands !== null) {
            return $this->inMemoryCommands->where('success', false)->first();
        }

        return $this->commands()->where('success', false)->first();
    }

    /**
     * Check if all commands in the batch were successful.
     */
    public function allCommandsSuccessful(): bool
    {
        if ($this->inMemoryMode && $this->inMemoryCommands !== null) {
            return $this->inMemoryCommands->where('success', false)->isEmpty();
        }

        return $this->commands()->where('success', false)->doesntExist();
    }

    /**
     * Check if the last command in the batch was successful.
     */
    public function wasLastCommandSuccessful(): bool
    {
        if ($this->inMemoryMode && $this->inMemoryCommands !== null) {
            $lastCommand = $this->inMemoryCommands->sortByDesc('id')->first();

            return $lastCommand ? $lastCommand->success : false;
        }

        $lastCommand = $this->commands()->orderBy('id', 'desc')->first();

        return $lastCommand ? $lastCommand->success : false;
    }

    /**
     * Associate commands with this batch.
     */
    public function associateCommand(CommandResult $commandResult)
    {
        if ($this->inMemoryMode) {
            $commandResult->batch_id = $this->id ?? 1;

            if ($this->inMemoryCommands === null) {
                $this->inMemoryCommands = collect([]);
            }

            $this->inMemoryCommands->push($commandResult);
            $this->setRelation('commands', $this->inMemoryCommands);

            return;
        }

        $commandResult->associateBatch($this);
        $this->load('commands');
    }

    /**
     * Associate commands with this batch.
     */
    public function associateCommands(Collection $commandResults)
    {
        if ($this->inMemoryMode) {
            if ($this->inMemoryCommands === null) {
                $this->inMemoryCommands = collect([]);
            }

            foreach ($commandResults as $commandResult) {
                $commandResult->batch_id = $this->id ?? 1;
                $this->inMemoryCommands->push($commandResult);
            }

            $this->setRelation('commands', $this->inMemoryCommands);

            return;
        }

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
