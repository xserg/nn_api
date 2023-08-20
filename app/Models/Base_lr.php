<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Location",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"lr"},
 *             @OA\Property(property="lr", type="integer"),
 *             required={"name"},
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="parent_id", type="string"),
 *             @OA\Property(property="parent_string", type="string"),
 *             @OA\Property(property="sort", type="integer"),
 *             @OA\Property(property="pop", type="integer"),
 *         )
 *     }
 * )
 */
class Base_lr extends Model
{
    use HasFactory;
    
    protected $table = 'base_lr';
    public $timestamps = false;
    protected $primaryKey = 'lr';

    protected $fillable = [
        'lr',
        'parent_id',
        'parent_string',
        'name',
        'sort',
        'pop',
    ];
}
