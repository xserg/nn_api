<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Groups",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"sid"},
 *             @OA\Property(property="gid", format="int64", type="integer"),
 *             required={"uid"},
 *             @OA\Property(property="uid", format="int64", type="integer"),
 *             @OA\Property(property="group_name", type="string"),
 *             @OA\Property(
 *                  property="value", type="array",
 *                  
 *                  @OA\Items( @OA\Property( type="string"), 
 *                  example={
 *                      "skip_already":0,
 *                      "use_yandex_xml":0,
 *                      "select_gid":0,
 *                      "f_use_base":1,
 *                      "f_skip_already":1,
 *                      "f_check_old":1,
 *                  },)
 *                ),
 *         )
 *     }
 * )
 */
class Control_groups extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'gid';
    protected $connection = 'db_control';
    
    protected $fillable = [
        'gid',
        'uid',
        'group_name',
    ];
    
    
}
