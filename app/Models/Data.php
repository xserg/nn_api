<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Data",
 *     type="object",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="pid", type="integer"),
 *             required={"title"},
 *             @OA\Property(property="title", type="string"),
 *             required={"name"},
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="ident", type="string"),
 *             @OA\Property(property="slug", type="string"),
 *             @OA\Property(property="sort", type="integer"),
 *         )
 *     }
 * )
 */
class Data extends Model
{
    use HasFactory;

    protected $table = 'plugins_data';
    public $timestamps = false;
    protected $primaryKey = 'pid';
    protected $connection = 'db_plugins';

    protected $fillable = [
        'pid',
        'title',
        'ident',
        'name',
        'slug',
    ];
}
