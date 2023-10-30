<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Keys",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"kid"},
 *             @OA\Property(property="kid", type="integer"),
 *             @OA\Property(property="uid", type="integer"),
 *             @OA\Property(property="pid", type="integer"),
 *             required={"domain"},
 *             @OA\Property(property="domain", type="string"),
 *             @OA\Property(property="callback", type="string"),
 *             @OA\Property(property="db_key", type="string"),
 *             @OA\Property(property="status", type="integer"),
 *             @OA\Property(property="expired", type="integer"),
 *             @OA\Property(property="bans", type="integer"),
 *             @OA\Property(property="version", type="string"),
 *         )
 *     }
 * )
 */
class Keys extends Model
{
    use HasFactory;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'last_time';

    protected $table = 'plugins_keys';
    //public $timestamps = false;
    protected $primaryKey = 'kid';
    protected $connection = 'db_plugins';

    protected $fillable = [
        'uid',
        'pid',
        'domain',
        'callback',
        'db_key',
        'status',
        'expired',
        'bans',
        'version',
    ];
}
