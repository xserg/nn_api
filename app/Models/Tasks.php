<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     allOf={
 *         @OA\Schema(            
 *             @OA\Property(property="tid", type="integer"),             
 *             @OA\Property(property="uid", type="integer"),
 *             @OA\Property(property="create_time", type="datetime"),
 *             @OA\Property(property="last_time", type="datetime"), 
 *             @OA\Property(property="stop_time", type="datetime"),  
 *             @OA\Property(property="engine", type="string", example="aparser"),
 *             @OA\Property(property="type", type="string", example="freq"),
 *             @OA\Property(property="cid", type="integer"),  
 *             @OA\Property(property="amount", type="integer"),
 *             @OA\Property(property="success", type="integer"),
 *             @OA\Property(property="fail", type="integer"),
 *             @OA\Property(property="nodata", type="integer"),    
 *             @OA\Property(property="data", type="string"),
 *             @OA\Property(property="output", type="string"),
 *             @OA\Property(property="progress", type="integer"), 
 *             @OA\Property(property="status", type="string", example="new"), 
 *         )
 *     }
 * )
 */
class Tasks extends Model
{
    use HasFactory;
    
    protected $table = 'main_tasks';
    public $timestamps = false;
    protected $primaryKey = 'tid';
    protected $connection = 'db_main';    

    protected $fillable = [
        //'tid',
        'uid',
        'create_time',
        'last_time',
        'stop_time',
        'engine',        
        'type',
        'cid',
        'amount',
        'success',
        'fail',
        'nodata',
        'data',        
        'output',
        'progress',
        'status'
    ];
}
