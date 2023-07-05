<?php

namespace Sunmking\DysmsSdk\Utils;

class Utils
{
    public static function result($params)
    {
        $res = [
            'code' => 404,
            'message' => '发生未知错误'
        ];
        $msg = StateCode::getMsg();
        $code = isset($params['Code']) ? $params['Code'] : 0;
        if(array_key_exists($code, $msg)){
            $res = $msg[$code];
        }else{
            $res = ['code'=>$code,'message' =>$params['Message']];
        }

        return json_encode($res);
    }


}