<?php

namespace PauloHortelan\OltMonitoring\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PauloHortelan\OltMonitoring\Models\Dio
 *
 * @property string $name
 * @property int $olt_id
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
    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }
}
