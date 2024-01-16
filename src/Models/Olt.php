<?php

namespace PauloHortelan\OltMonitoring\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PauloHortelan\OltMonitoring\Models\Olt
 *
 * @property string $name
 * @property string $host
 * @property string $username
 * @property string $password
 * @property string $brand
 * @property string $product_model
 */
class Olt extends Model
{
    use HasFactory;

    // Disable Laravel's mass assignment protection
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
        'product_model',
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
}
