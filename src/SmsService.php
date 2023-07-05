<?php

namespace Sunmking\DysmsSdk;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendDetailsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchSmsRequest;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Sunmking\DysmsSdk\Exceptions\InvalidConfigException;
use Sunmking\DysmsSdk\Utils\Utils as DyUtils;

class SmsService
{
    // 短信API产品域名
    protected $domain = "dysmsapi.aliyuncs.com";
    // 暂时不支持多Region
    protected $region = "cn-hangzhou";
    // accessKeyId
    public $accessKeyId;
    // accessKeySecret
    public $accessKeySecret;
    // aceClient
    public $client;

    /**
     * @throws InvalidConfigException
     */
    public function __construct($accessKeyId,$accessKeySecret,$domain,$region)
    {
        if(!isset($accessKeyId)){
            throw new InvalidConfigException('accessKeyId can not be blank.');
        }else{
            $this->accessKeyId = $accessKeyId;
        }
        if(!$accessKeySecret){
            throw new InvalidConfigException('accessKeySecret can not be blank.');
        }else{
            $this->accessKeySecret = $accessKeySecret;
        }
        if(!$domain){
            $domain = $this->domain;
        }
        if(!$region){
            $region = $this->region;
        }
        $this->client = self::createClient($accessKeyId,$accessKeySecret,$domain,$region);
    }

    /**
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $domain
     * @param $region
     * @return Dysmsapi
     */
    public static function createClient($accessKeyId, $accessKeySecret,$domain,$region)
    {
        $config = new Config([
            // 必填，您的 AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 必填，您的 AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        $config->endpoint = $domain;
        $config->regionId = $region;
        return new Dysmsapi($config);
    }

    public function sendSms($signName, $templateCode, $phoneNumbers, $templateParam = null, $outId = null) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置雉短信接收号码
        $request->phoneNumbers = $phoneNumbers;

        // 必填，设置签名名称
        $request->signName = $signName;

        // 必填，设置模板CODE
        $request->templateCode = $templateCode;

        // 可选，设置模板参数
        if($templateParam) {
            $request->templateParam = json_encode($templateParam);
        }

        // 可选，设置流水号
        if($outId) {
            $request->outId = $outId;
        }

        // 发起访问请求
        $acsResponse = $this->client->sendSms($request);
        // 打印请求结果
        $acsResponse = $acsResponse->body->toMap();
        if(array_key_exists('Message', $acsResponse) && $acsResponse['Code']=='OK'){
            return json_encode([
                'code' => 200,
                'message' => '验证码发送成功'
            ]);
        }
        return DyUtils::result($acsResponse);
    }

    /**
     * 批量发送短信
     * @param $signName
     * @param $templateCode
     * @param $phoneNumbers
     * @param null $templateParam
     * @return false|string
     */
    public function sendBatchSms($signName, $templateCode, $phoneNumbers, $templateParam = null) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendBatchSmsRequest();

        //可选-启用https协议
        //$request->setProtocol("https");

        // 必填:待发送手机号。支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->phoneNumberJson = json_encode($phoneNumbers, JSON_UNESCAPED_UNICODE);

        // 必填:短信签名-支持不同的号码发送不同的短信签名
        $request->signNameJson=json_encode($signName, JSON_UNESCAPED_UNICODE);

        // 必填:短信模板-可在短信控制台中找到
        $request->templateCode = $templateCode;

        // 必填:模板中的变量替换JSON串,如模板内容为"亲爱的${name},您的验证码为${code}"时,此处的值为
        // 友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
        $request->templateParamJson = json_encode($templateParam, JSON_UNESCAPED_UNICODE);

        // 可选-上行短信扩展码(扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段)
        // $request->setSmsUpExtendCodeJson("[\"90997\",\"90998\"]");

        // 发起访问请求
        $acsResponse = $this->client->sendBatchSms($request);
        // 打印请求结果
        $acsResponse = $acsResponse->body->toMap();
        if(array_key_exists('Message', $acsResponse) && $acsResponse['Code']=='OK'){
            return json_encode([
                'code' => 200,
                'message' => '验证码发送成功'
            ]);
        }
        return DyUtils::result($acsResponse);
    }

    /**
     * 查询短信发送情况范例
     *
     * @param string $phoneNumbers 必填, 短信接收号码 (e.g. 12345678901)
     * @param string $sendDate 必填，短信发送日期，格式Ymd，支持近30天记录查询 (e.g. 20170710)
     * @param int $pageSize 必填，分页大小
     * @param int $currentPage 必填，当前页码
     * @param string $bizId 选填，短信发送流水号 (e.g. abc123)
     */
    public function queryDetails($phoneNumbers, $sendDate, $pageSize = 10, $currentPage = 1, $bizId=null) {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->phoneNumber = $phoneNumbers;

        // 选填，短信发送流水号
        $request->bizId = $bizId;

        // 必填，短信发送日期，支持近30天记录查询，格式Ymd
        $request->sendDate = $sendDate;

        // 必填，分页大小
        $request->pageSize = $pageSize;

        // 必填，当前页码
        $request->currentPage = $currentPage;

        // 发起访问请求
        // 打印请求结果
        // var_dump($acsResponse);

        return $this->client->querySendDetails($request);
    }
}