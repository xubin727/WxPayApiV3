<?php
namespace Xubin\WxPayApiV3\Native;

use WechatPay\GuzzleMiddleware\Util\PemUtil;


class DemoConfig extends Config {
        
    /**
     * 商户号
     */
    public function getMerchantId()
    {
        $merchantId = '1605721228';
        
        return $merchantId;
    }
    
    /**
     * 商户API证书序列号
     */
    public function getMerchantSerialNumber()
    {
        $merchantSerialNumber = '26840DA97A7F37723233DD335613E1E95F9F32E9';
        
        return $merchantSerialNumber;
    }
    
    /**
     * 商户私钥
     */
    public function getMerchantPrivateKey()
    {
        $merchantPrivateKey = PemUtil::loadPrivateKey(BASE_DIR. '/../../programs/wxpay/WXCertUtil/cert/apiclient_key.pem');
        
        return $merchantPrivateKey;
    }
    
    /**
     * 微信支付平台证书
     */
    public function getWechatpayCertificate()
    {
        $wechatpayCertificate = PemUtil::loadCertificate(BASE_DIR. '/../../programs/wxpay/WXCertUtil/cert/apiclient_cert.pem');
        
        return $wechatpayCertificate;
    }
    
    /**
     * Appid
     */
    public function getAppId()
    {
        return 'wxc1134a1cdd87716b';
    }
    
}


