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
    ];

    protected $casts = [
        'success' => 'boolean',
        'result' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CommandResultBatch::class, 'batch_id');
    }

    public function associateBatch(CommandResultBatch $commandResultBatch)
    {
        $this->batch()->associate($commandResultBatch);
        $this->save();
    }
}
