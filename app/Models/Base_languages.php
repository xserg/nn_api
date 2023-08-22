<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Language",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             required={"lang"},
 *             @OA\Property(property="lang", type="string"),
 *             required={"name"},
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="aparser", type="string"),
 *             @OA\Property(property="three", type="string"),
 *             @OA\Property(property="rucaptcha", type="integer"),
 *         )
 *     }
 * )
 */
class Base_languages extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'lang';
    protected $connection = 'mysql3';

    protected $fillable = [
        'lang',
        'name',
        'aparser',
        'three',
        'rucaptcha',
    ];
}
