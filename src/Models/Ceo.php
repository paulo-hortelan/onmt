<?php

namespace PauloHortelan\OltMonitoring\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PauloHortelan\OltMonitoring\Models\Ceo
 *
 * @property string $name
 * @property int $olt_id
 */
class Ceo extends Model
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
        'dio_id',
    ];

    /**
     * Get the DIO associated with the CEO.
     */
    public function dio()
    {
        return $this->belongsTo(Dio::class);
    }

    /**
     * Get the CEO Splitters from the associated CEO
     */
    public function ceo_splitters(): HasMany
    {
        return $this->hasMany(CeoSplitter::class);
    }
}
