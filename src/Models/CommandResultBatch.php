<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommandResultBatch extends Model
{
    protected $table = 'command_result_batches';

    const UPDATED_AT = null;

    protected $fillable = [
        'ip',
        'description',
        'interface',
        'serial',
        'operator',
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
}
