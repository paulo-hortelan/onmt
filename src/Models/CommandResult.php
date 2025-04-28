<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandResult extends Model
{
    protected $table = 'command_results';

    const UPDATED_AT = null;

    protected $fillable = [
        'success',
        'command',
        'response',
        'error',
        'result',
        'batch_id',
        'created_at',
        'finished_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'result' => 'array',
        'created_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected static bool $databaseTransactionsEnabled = true;

    public static function disableDatabaseTransactions(): void
    {
        self::$databaseTransactionsEnabled = false;
    }

    public static function enableDatabaseTransactions(): void
    {
        self::$databaseTransactionsEnabled = true;
    }

    public static function areDatabaseTransactionsEnabled(): bool
    {
        return self::$databaseTransactionsEnabled;
    }

    public static function create(array $attributes = [])
    {
        if (! self::$databaseTransactionsEnabled) {
            return new static($attributes);
        }

        return parent::create($attributes);
    }

    public static function make(array $attributes = [])
    {
        $model = new static($attributes);

        if (self::$databaseTransactionsEnabled) {
            $model->save();
        }

        return $model;
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CommandResultBatch::class, 'batch_id');
    }

    public function associateBatch(CommandResultBatch $commandResultBatch)
    {
        $this->batch()->associate($commandResultBatch);

        if (self::$databaseTransactionsEnabled) {
            $this->save();
        }
    }

    public function executionTimeInSeconds(): ?int
    {
        if ($this->finished_at && $this->created_at) {
            return $this->finished_at->diffInSeconds($this->created_at);
        }

        return null;
    }
}
