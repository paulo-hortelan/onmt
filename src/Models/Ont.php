<?php

namespace PauloHortelan\OltMonitoring\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PauloHortelan\OltMonitoring\Models\Ont
 *
 * @property string $name
 * @property string $interface
 * @property string $oid
 * @property int $cto_id
 * @property-read Cto $cto
 */
class Ont extends Model
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
        'interface',
        'cto_id',
    ];

    /**
     * Get the CTO associated with the ONT.
     */
    public function cto(): BelongsTo
    {
        return $this->belongsTo(Cto::class);
    }
}
