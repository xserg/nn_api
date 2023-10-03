<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Notify",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"uid"},
 *             @OA\Property(property="nid", type="integer"),
 *             required={"type"},
 *             @OA\Property(property="uid", type="integer"),
 *             required={"name"}, 
 *             @OA\Property(property="type", type="string", example={"default", "success", "info", "primary", "warning", "danger", "dark"}),
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
class Notify extends Model
{
    use HasFactory;
    
    protected $table = 'main_notify';
    public $timestamps = false;
    protected $primaryKey = 'nid';
    protected $connection = 'db_main';    

    protected $fillable = [
        'nid',
        'uid',
        'type',
        'group_name',
        'create_time',
        'read_time',
        'message',
        'data',
        'status'
    ];
}
