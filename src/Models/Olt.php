<?php

namespace PauloHortelan\Onmt\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PauloHortelan\Onmt\Models\Olt
 *
 * @property string $name
 * @property string $host
 * @property string $username
 * @property string $password
 * @property string $brand
 * @property string $model
 */
class Olt extends Model
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
        'host',
        'username',
        'password',
        'brand',
        'model',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'username' => 'encrypted',
        'password' => 'encrypted',
    ];

    /**
     * Get the DIO's from the associated OLT
     */
    public function dios(): HasMany
    {
        return $this->hasMany(Dio::class);
    }
}
