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
 *             required={"uid"},
 *             @OA\Property(property="nid", type="integer"),
 *             required={"name"},
 *             @OA\Property(property="uid", type="integer"),
 *             required={"name"}, 
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="group_name", type="string"),
 *             @OA\Property(property="create_time", type="datetime"),
 *             @OA\Property(property="resd_time", type="datetime"), 
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="string"),
 *             @OA\Property(property="status", type="string"), 
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
        'tid',
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
