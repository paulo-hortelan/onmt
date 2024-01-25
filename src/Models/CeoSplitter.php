<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PauloHortelan\Onmt\Models\CeoSplitter
 *
 * @property string $name
 * @property string $type
 * @property int $slot
 * @property int $pon
 * @property int $ceo_id
 * @property-read Ceo $ceo
 */
class CeoSplitter extends Model
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
        'slot',
        'pon',
        'ceo_id',
    ];

    /**
     * Get the CEO associated with the CEO Splitter.
     */
    public function ceo(): BelongsTo
    {
        return $this->belongsTo(Ceo::class);
    }

    /**
     * Get the CTO's from the associated CEO Splitter
     */
    public function ctos(): HasMany
    {
        return $this->hasMany(Cto::class);
    }
}
