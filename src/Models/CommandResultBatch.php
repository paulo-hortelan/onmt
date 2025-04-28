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

    protected static bool $databaseTransactionsEnabled = true;

    public static function disableDatabaseTransactions(): void
    {
        self::$databaseTransactionsEnabled = false;
        CommandResult::disableDatabaseTransactions();
    }

    public static function enableDatabaseTransactions(): void
    {
        self::$databaseTransactionsEnabled = true;
        CommandResult::enableDatabaseTransactions();
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

    public function save(array $options = [])
    {
        if (! self::$databaseTransactionsEnabled) {
            return $this;
        }

        return parent::save($options);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(CommandResult::class, 'batch_id');
    }

    public function firstError()
    {
        return $this->commands()->where('success', false)->first();
    }

    public function allCommandsSuccessful(): bool
    {
        return $this->commands()->where('success', false)->doesntExist();
    }

    public function wasLastCommandSuccessful(): bool
    {
        $lastCommand = $this->commands()->orderBy('id', 'desc')->first();

        return $lastCommand ? $lastCommand->success : false;
    }

    public function associateCommand(CommandResult $commandResult)
    {
        $commandResult->associateBatch($this);

        $this->load('commands');
    }

    public function associateCommands(Collection $commandResults)
    {
        foreach ($commandResults as $commandResult) {
            $commandResult->associateBatch($this);
        }

        $this->load('commands');
    }

    public function executionTimeInSeconds(): ?int
    {
        if ($this->finished_at && $this->created_at) {
            return $this->finished_at->diffInSeconds($this->created_at);
        }

        return null;
    }
}
