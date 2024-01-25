<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * PauloHortelan\Onmt\Models\Cto
 *
 * @property string $name
 * @property string $type
 * @property int $ceo_splitter_id
 * @property-read CeoSplitter $ceo_splitter
 */
class Cto extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'ceo_splitter_id',
    ];

    /**
     * Get the CEO Splitter associated with the CTO.
     */
    public function ceo_splitter(): BelongsTo
    {
        return $this->belongsTo(CeoSplitter::class);
    }

    /**
     * Get the CEO associated with the CTO.
     */
    public function ceo(): HasOne
    {
        return $this->hasOne(Ceo::class);
    }
}
