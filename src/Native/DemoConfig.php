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
        $merchantSerialNumber = '13BD2F544AC3E35816FC6149A7B4722DC67CD1D4';
        
        return $merchantSerialNumber;
    }
    
    /**
     * 商户私钥
     */
    public function getMerchantPrivateKey()
    {
        $merchantPrivateKey = PemUtil::loadPrivateKey('‪D:/programs/wxpay/WXCertUtil/cert/apiclient_key.pem');
        
        return $merchantPrivateKey;
    }
    
    /**
     * 微信支付平台证书
     */
    public function getWechatpayCertificate()
    {
        $wechatpayCertificate = PemUtil::loadCertificate('‪D:/programs/wxpay/WXCertUtil/cert/apiclient_cert.pem');
        
        return $wechatpayCertificate;
    }
    
}


