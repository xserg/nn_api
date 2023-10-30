<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Pin",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"pin"},
 *             @OA\Property(property="kid", type="integer"),
 *             @OA\Property(property="pin", type="integer"),
 *             @OA\Property(property="expired_time", type="datetime"),
 *         )
 *     }
 * )
 */
class Pins extends Model
{
    use HasFactory;

    protected $table = 'plugins_pins';
    public $timestamps = false;
    protected $primaryKey = 'kid';
    protected $connection = 'db_plugins';

    protected $fillable = [
        'kid',
        'pin',
        'expired_time',
    ];
}
