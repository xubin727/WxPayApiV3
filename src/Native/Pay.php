<?php
namespace Xubin\WxPayApiV3\Native;

use GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Client;
use \GuzzleHttp\HandlerStack;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;
use Xubin\WxPayApiV3\Loger\Log;
use Xubin\WxPayApiV3\Loger\CLogFileHandler;
use WechatPay\GuzzleMiddleware\Util\AesUtil;


class Pay {

    protected $config = null;
    protected $param = null;
    protected $logPath = '';
    protected $loger = null;
    protected $httpClient = null;

    /**
     * Native 支付方式
     * @param Config $config
     * @return \Xubin\WxPayApiV3\Native\Pay
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        
        $merchantId = $this->config->getMerchantId();
        $merchantSerialNumber = $this->config->getMerchantSerialNumber();
        $merchantPrivateKey = $this->config->getMerchantPrivateKey();
        $wechatpayCertificate = $this->config->getWechatpayCertificate();

        // 构造一个WechatPayMiddleware
        $wechatpayMiddleware = WechatPayMiddleware::builder()
        ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey) // 传入商户相关配置
        ->withWechatPay([ $wechatpayCertificate ]) // 可传入多个微信支付平台证书，参数类型为array
        ->build();

        // 将WechatPayMiddleware添加到Guzzle的HandlerStack中
        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');

        // 创建Guzzle HTTP Client时，将HandlerStack传入
        $this->httpClient = new \GuzzleHttp\Client(['handler' => $stack]);

        return $this;
    }

    /**
     * 设置支付日志路径
     * @param string $path
     * @return \Xubin\WxPayApiV3\Native\Pay
     */
    public function setLogPath($path)
    {
//         $logPath = realpath( Yii::app()->basePath . "/../runtime/paylogs" ) . '/nativepaylog_' .date('Y_m_d').'.log';
        $this->logPath = $path;
        
        $this->initLoger();

        return $this;
    }

    /**
     * 初始化日志对象
     * @return \Xubin\WxPayApiV3\Loger\Log
     */
    protected function initLoger()
    {
        //初始化日志
        $logHandler= new CLogFileHandler($this->logPath);
        $this->loger = Log::Init($logHandler, 15);

        return $this->loger;
    }

    /**
     * 生成Native方式支付订单
     * @param number $amount 支付金额
     * @param string $orderId 商家订单ID
     * @param string $notifyUrl 支付成功后的回调地址
     * @param string $orderDesc 订单描述信息
     * @param string $logPath 日志路径
     * @param array $params 其它参数
     * @param array $headers 支付请求向支付服务器发送的头信息
     * @return mixed|array 成功则返回正常的json信息，否则返回一个空的数组
     */
    public function createOrder($amount, $orderId, $notifyUrl, $orderDesc, $logPath, $params=[], $headers=[])
    {

        $this->setLogPath($logPath);
        $loger = $this->loger;

//         $prod_id = '123';
        $loger->INFO($this->logPath);
        
        $str = json_encode([
            'appid' => $this->config->getAppId(),
            'mchid' => $this->config->getMerchantId(),
            'description' => $orderDesc,
            'out_trade_no' => $orderId,
            'notify_url' => $notifyUrl,
            'amount' => [ 'currency'=>'CNY', 'total' => $amount ],]);

        // 接下来，正常使用Guzzle发起API请求，WechatPayMiddleware会自动地处理签名和验签
        try {
            $client = $this->httpClient;
            
            $resp = $client->request('POST', 'https://api.mch.weixin.qq.com/v3/pay/transactions/native', [
                'json' => [ // JSON请求体
                    'appid' => $this->config->getAppId(),
                    'mchid' => $this->config->getMerchantId(),
                    'description' => $orderDesc,
                    'out_trade_no' => $orderId,
                    'notify_url' => $notifyUrl,
                    'amount' => [ 'currency'=>'CNY', 'total' => $amount ],
//                     'time_expire' => '', //从这个开始，后面的均为非必须项
//                     'attach' => '',
//                     'goods_tag' => '',
//                     'detail' => [ 'cost_price'=>'', 'invoice_id'=>'' ],
//                     'scene_info' => [
//                         "store_info" => [
//                             "address" => "广东省深圳市南山区科技中一道10000号",
//                             "area_code" => "440305",
//                             "name" => "腾讯大厦分店",
//                             "id" => "0001"
//                         ],
//                         "device_id" => "013467007045764",
//                         "payer_client_ip" => "14.23.150.211"
//                     ],
//                     'settle_info' => [ 'profit_sharing' => '' ],
                ],
                'headers' => [ 'Accept' => 'application/json' ]
            ]);
            $loger->INFO( $resp->getStatusCode().' '.$resp->getReasonPhrase());
            return json_decode($resp->getBody(), JSON_OBJECT_AS_ARRAY);


        } catch (RequestException $e) {
            // 进行错误处理
            $loger->ERROR( $e->getMessage().'json:' .$str );
            if ($e->hasResponse()) {
                $loger->INFO( $e->getResponse()->getStatusCode().' '.$e->getResponse()->getReasonPhrase().' '.$e->getResponse()->getBody() );
            }
            return json_decode($e->getResponse()->getBody(), JSON_OBJECT_AS_ARRAY);
        }


    }
    
    /**
     * 回调解密内容
     * @param string $data
     * @param string $logPath
     * @return string|boolean
     */
    public function notifyDecode($data, $logPath='')
    {
        $this->setLogPath($logPath);
//         $loger = $this->loger;
        
        $data = json_decode($data, JSON_OBJECT_AS_ARRAY);
        $apiv3key = $this->config->getApiV3Key();
        $aes = new AesUtil($apiv3key);
        $res = $aes->decryptToString($data['resource']['associated_data'], $data['resource']['nonce'], $data['resource']['ciphertext']);
        
//         $loger->DEBUG( 'pay168:'. $res );
        
        return $res;
    }
    
    /**
     * 回调签名验证
     * @param string $postData 应答报文主体
     * @return bool|void
     */
    public function notifyCheck($postData)
    {
        $headers = getallheaders();
        \Yii::log("notify headers: " . json_encode($headers), 'info', 'pay');
        
        $WechatpaySerial = $headers['Wechatpay-Serial']; // 证书序列号
        if ($this->config->getMerchantSerialNumber() != $WechatpaySerial) { // 证书序列号不正确，直接退出 ?
            /**
             * @todo 重新获取证书
             */
            //exit;
        }
        $WechatpayTimestamp = $headers['Wechatpay-Timestamp']; // 应答时间戳
        $WechatpayNonce = $headers['Wechatpay-Nonce']; // 应答随机串
        $WechatpaySignature = $headers['Wechatpay-Signature']; // 接收到的签名
        $string = $WechatpayTimestamp . "\n" . $WechatpayNonce . "\n" . $postData . "\n"; // 验签名串
        $signature = base64_decode($WechatpaySignature); // 解密后的应答签名
        /**
         * @todo 签名验证
         */
        
        return true;
    }

}






