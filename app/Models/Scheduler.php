<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Scheduler",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="sid", type="integer"),
 *             @OA\Property(property="uid", type="integer"),
 *             @OA\Property(property="cid", type="integer"),
 *             required={"uid"}, 
 *             @OA\Property(property="controller", type="string"),
 *             @OA\Property(property="group_name", type="string"),
 *             @OA\Property(property="last_time", type="datetime"),
 *             @OA\Property(property="next_time", type="datetime"), 
 *             @OA\Property(property="week", type="string"),
 *             @OA\Property(property="month", type="string"),
 *             @OA\Property(property="time", type="string"),
 *             @OA\Property(property="input", type="string"),  
 *         )
 *     }
 * )
 */
class Scheduler extends Model
{
    use HasFactory;
    
    protected $table = 'main_scheduler';
    public $timestamps = false;
    protected $primaryKey = 'sid';
    protected $connection = 'db_main';    

    protected $fillable = [
        'sid',	
        'uid',	
        'cid',	
        'controller',	
        'last_time',
        'next_time',
        'week',
        'month',
        'time',
        'input'
    ];
}
