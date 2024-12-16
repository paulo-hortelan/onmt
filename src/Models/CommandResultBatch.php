<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommandResultBatch extends Model
{
    protected $table = 'command_result_batches';

    const UPDATED_AT = null;

    protected $fillable = [
        'interface',
        'serial',
        'commands',
    ];

    public function commands(): HasMany
    {
        return $this->hasMany(CommandResult::class, 'batch_id');
    }

    /**
     * Check if all commands in the batch were successful.
     */
    public function allCommandsSuccessful(): bool
    {
        return $this->commands()->where('success', false)->doesntExist();
    }
}
