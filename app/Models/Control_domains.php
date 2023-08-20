<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Control_domain",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"cid"},
 *             @OA\Property(property="cid", format="int64", type="integer"),
 *             required={"uid"},
 *             @OA\Property(property="uid", format="int64", type="integer"),
 *             required={"did"},
 *             @OA\Property(property="did", format="int64", type="integer"),
 *             @OA\Property(property="yid", format="int64", type="integer"),
 *             @OA\Property(property="metrika_id", format="int64", type="integer"),
 *             @OA\Property(property="yandex_id", format="int64", type="integer"),
 *             @OA\Property(property="host_id", format="int64", type="integer"),
 *             @OA\Property(property="lr", format="int64", type="integer"),
 *             @OA\Property(property="gid", format="int64", type="integer"), 
 *             @OA\Property(property="pos_settings", type="string"),
 *         )
 *     }
 * )
 */
class Control_domains extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'cid';
    protected $connection = 'mysql2';
    
    protected $fillable = [
        'cid',
        'uid',
        'did',
        'yid',	
        'metrika_id',	
        'yandex_id',	
        'host_id',	
        'lr',
        'gid',
        'hidden',
        'is_scheduler',
        'request_all',
        'request_trash',
        'pos_settings',
        'external_excluded'
    ];
    
    
}
