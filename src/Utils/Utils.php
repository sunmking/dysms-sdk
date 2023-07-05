<?php

namespace Sunmking\DysmsSdk\Utils;

class Utils
{
    /**
     * @param $params
     * @return array
     */
    public static function result($params)
    {
        $msg = StateCode::getMsg();
        $code = isset($params['Code']) ? $params['Code'] : 404;
        if(array_key_exists($code, $msg)){
            $res = $msg[$code];
        }else{
            $res = ['code'=>$code,'message' =>$params['Message']];
        }
        return $res;
    }
}