<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class WxModel extends Model
{
   public $table = 'p_wx_users';
        public $timestamps = false;

    protected static $redis_weixin_access_token = 'str:weixin_access_token';
    public static function getWXAccessToken()
    {

        //获取缓存
        $token = Redis::get(self::$redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set(self::$redis_weixin_access_token,$token);
            Redis::setTimeout(self::$redis_weixin_access_token,3600);
        }
        return $token;

    }
    // 刷新access_token

    public function refreshToken()
    {
        Redis::del($this->redis_weixin_access_token);
        echo $this->getWXAccessToken();
    }

}
