<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PauloHortelan\Onmt\Models\Dio
 *
 * @property string $name
 * @property int $olt_id
 * @property-read Olt $olt
 */
class Dio extends Model
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
        'olt_id',
    ];

    /**
     * Get the OLT associated with the DIO.
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    /**
     * Get the CEO's from the associated DIO
     */
    public function ceos(): HasMany
    {
        return $this->hasMany(Ceo::class);
    }
}
