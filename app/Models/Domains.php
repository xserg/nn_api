<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Domain",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Domain"),
 *         @OA\Schema(
 *             required={"did"},
 *             @OA\Property(property="did", format="int64", type="integer"),
 *             required={"domain"},
 *             @OA\Property(property="domain", type="string"),
 *             example={"did": 10, "domain": "google.com"}
 *         )
 *     }
 * )
 */
class Domains extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'did';
    protected $connection = 'db_base';
    protected $table = 'base_domains';

    protected $fillable = [
        'domain',
    ];
  
    public function store($domain, $check_exists=true)
    {
        $this->did = '';
        $this->domain = self::trim($domain);  
        
        if ($res = $this->where('domain', $this->domain)->first()) {
            //$this->error = 'Domain already exist.';
            $this->did = $res->did;      
            return 508;
        } 
        $check = $this->check_domain_name($this->domain, $check_exists);  
               
        if ($check != 200) {
            return $check; 
        }
        $res = $this->create(['domain' => $this->domain]);
        $this->did = $res->did; 
        return 200;
    }
    
    
    public function check_domain_name($domain, $check_exists=true)
    {
      //$this->error = '';
      $check = $this->correct($domain);
      if ($check != 200) {
        return $check; 
      } 
      // корректность домена
      if ($check_exists && !self::exists($domain)) { 
        return 501; 
      } // домен не зареген в сети интернет
      return 200;
    }
    
    private static function trim($domain)
    {
      $output=trim($domain);
      $output = mb_strtolower($output);
      if (!preg_match('/^(http(s)?:\/\/)/u', $output)) { 
        $output='http://'.$output; 
      }
      $output = parse_url($output, PHP_URL_HOST);
       // переведем в utf8, если поддомен кириллический
      $output = self::to_utf8($output);
      // оставим только utf8 символы
      $output = @iconv('utf-8', 'utf-8//IGNORE', $output); 
      // удалить нечитаемые символы
      $output = preg_replace('/[\x00-\x1F\x7F]/u', '', $output); 
      $output = trim($output);
      $output = preg_replace('/^www\./i', '', $output);
      return $output;
    }
  
    function correct($domain)
    {
      if (mb_strlen($domain)<3)	{ 
        //$this->error = "Domain too short: ".htmlspecialchars($domain); 
        return 502; 
      } else if (mb_strlen($domain)>255) { 
        //$this->error = "Domain too long"; 
        return 503; 
      } else if ($domain[0]=="-" || $domain[0]==".") { 
        //$this->error = "Domain should start from letter: ".htmlspecialchars($domain); 
        return 504; 
      } else if (!preg_match("/^(.*)\.[\S]{2,}$/", $domain)) { 
        //$this->error = "Wrong domain: ".htmlspecialchars($domain); 
        return 505; 
      } else if (preg_match("/[!@#$%^&*() +=~`<>?,|\"';:\/\\\[\]\{\}]/", $domain)) { 
        //$this->error = "Incorect symbols: ".htmlspecialchars($domain); 
        return 506; 
      } else {
        return 200;
      }
    }
    
    private static function exists($domain)
    {
      $domain = self::to_ascii($domain);
      $ip = gethostbyname($domain); 
      if ($ip==$domain) { 
          return false; 
      }
      if (!filter_var($ip, FILTER_VALIDATE_IP)) { 
          return false; 
      }
      return true;
    }
    
    
    /**
     * Перевести домен в ascii (не затронет латинские домены)
     * сайт.ру => xn--80aswg.xn--p1ag
     *
     * @param string $domain
     * @return string
     */
    private static function to_ascii(string $domain)
    {
      if (preg_match('#[а-яё]#iu', $domain)) { $domain=idn_to_ascii($domain); }
      return $domain;
    }
    
    /**
     * Перевести домен в utf8
     * xn--80aswg.xn--p1ag => сайт.ру
     *
     * @param string $domain
     * @return string
     */
    private static function to_utf8(string $domain)
    {
      $output=idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
      return $output ? $output : $domain;
    }
}
