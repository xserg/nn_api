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
class Base_domains extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'did';
    protected $connection = 'mysql3';

    protected $fillable = [
        'domain',
    ];
  
    public function store($domain, $check_exists=true)
    {
        $this->error = '';
        $this->did = '';
        $this->domain = $this->trim($domain);    
        //$this->did = 0;
        if ($res = $this->where('domain', $this->domain)->first()) {
            $this->error = 'Domain already exist.';
            $this->did = $res->did;      
            return false;
        }          
        if (!$this->check_domain_name($this->domain, $check_exists)) {
            return false; 
        }
        $res = $this->create(['domain' => $this->domain]);
        $this->did = $res->did; 
        //$this->save();
        return true;
    }
    
    
    public function check_domain_name($domain, $check_exists=true)
    {
      if (!$this->correct($domain)) { return false; } // корректность домена
      if ($check_exists && !$this->exists($domain)) { return false; } // домен не зареген в сети интернет
      return true;
    }
    
    public static function trim($domain)
    {
      $output=trim($domain);
      $output = mb_strtolower($output);
      if (!preg_match('/^(http(s)?:\/\/)/u', $output)) { $output='http://'.$output; }
      $output = parse_url($output, PHP_URL_HOST); if (!$output) { $this->error="Host error"; return false; }
      $output = self::to_utf8($output); // переведем в utf8, если поддомен кириллический
      $output = @iconv('utf-8', 'utf-8//IGNORE', $output); // оставим только utf8 символы
      $output = preg_replace('/[\x00-\x1F\x7F]/u', '', $output); // удалить нечитаемые символы
      $output = trim($output);
      $output = preg_replace('/^www\./i', '', $output);
      return $output;
    }
  
    function correct($domain)
    {
      if      (empty($domain))			{ $this->error = "Domain required"; return false; }
      else if (mb_strlen($domain)<3)		{ $this->error = "Domain too short: ".htmlspecialchars($domain); return false; }
      else if (mb_strlen($domain)>255)	{ $this->error = "Domain too long"; return false; }
      else if ($domain[0]=="-" || $domain[0]==".") { $this->error = "Domain should start from letter: ".htmlspecialchars($domain); return false; }
      //else if (strpos($domain, ".")===false) { $this->error = "Некорректный домен: ".htmlspecialchars($domain); return false; }
      else if (!preg_match("/^(.*)\.[\S]{2,}$/", $domain)) { $this->error = "Wrong domain: ".htmlspecialchars($domain); return false; }
      else if (preg_match("/[!@#$%^&*() +=~`<>?,|\"';:\/\\\[\]\{\}]/", $domain)) { $this->error = "Incorect symbols: ".htmlspecialchars($domain); return false; }
      return true;
    }
    
    public function exists($domain)
    {
      $not_exist_error = 'Domain not registered';
      $domain=self::to_ascii($domain);
      $ip = gethostbyname($domain); if ($ip==$domain) { $this->error = $not_exist_error; return false; }
      if (!filter_var($ip, FILTER_VALIDATE_IP)) { $this->error = $not_exist_error; return false; }
      return true;
    }
    
    /**
     * Перевести домен в ascii (не затронет латинские домены)
     * сайт.ру => xn--80aswg.xn--p1ag
     *
     * @param string $domain
     * @return string
     */
    public static function to_ascii(string $domain)
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
    public static function to_utf8(string $domain)
    {
      $output=idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
      return $output ? $output : $domain;
    }
}
