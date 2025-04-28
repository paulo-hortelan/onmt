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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CommandResultBatch::class, 'batch_id');
    }

    public function associateBatch(CommandResultBatch $commandResultBatch)
    {
        if ($commandResultBatch->inMemoryMode) {
            $this->batch_id = $commandResultBatch->id ?? 1;

            $commandResultBatch->associateCommand($this);

            return;
        }

        $this->batch()->associate($commandResultBatch);
        $this->save();
    }
}
