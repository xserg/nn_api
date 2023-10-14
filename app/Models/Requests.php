<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Request",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Request"),
 *         @OA\Schema(
 *             required={"rid"},
 *             @OA\Property(property="rid", format="int64", type="integer"),
 *             required={"domain"},
 *             @OA\Property(property="request", type="string"),
 *             example={"rid": 10, "request": "( 1 - 2 )"}
 *         )
 *     }
 * )
 */
class Requests extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'rid';
    protected $connection = 'db_base';
    protected $table = 'base_request';

    protected $fillable = [
        'request',
    ];
  
    public function store($request)
    {
        $this->rid = '';
        $this->request = self::trim($request);  
        $check = $this->check_request_name($request);           
        if ($check != 200) {
            return $check; 
        }
        $res = $this->create(['request' => $this->request]);
        $this->rid = $res->rid; 
        return 200;
    }
    
    private static function trim($request)
    {
      $output=trim($request);
      $output = mb_strtolower($output);
      $output=str_replace('ё','е', $output); 
      return $output;
    }
  
    public function check_request_name($request)
    {
      if (mb_strlen($request)<3)	{ 
        return 502; 
      } else if (mb_strlen($request)>255) { 
        return 503; 
      } else if (!preg_match('/^[a-zа-я0-9 \-_\(\)\?\.:\/]{1,255}$/iu', $request)) { 
        return 506; 
      } else if ($res = $this->where('request', $request)->first()) {
        $this->rid = $res->rid;      
        return 508;
      } else {
        return 200;
      }
    } 
    
}
