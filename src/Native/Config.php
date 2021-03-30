<?php
namespace Xubin\WxPayApiV3\Native;

abstract class Config {
    
    /*
    // 商户相关配置
    protected $merchantId = '1000100'; //
    protected $merchantSerialNumber = 'XXXXXXXXXX'; //
    protected $merchantPrivateKey = PemUtil::loadPrivateKey('/path/to/mch/private/key.pem'); //
    // 微信支付平台配置
    protected $wechatpayCertificate = PemUtil::loadCertificate('/path/to/wechatpay/cert.pem'); //
    */
    
    /**
     * 商户号
     */
    public abstract function getMerchantId();
    
    /**
     * 商户API证书序列号
     */
    public abstract function getMerchantSerialNumber();
    
    /**
     * 商户私钥
     */
    public abstract function getMerchantPrivateKey();
    
    /**
     * 微信支付平台证书
     */
    public abstract function getWechatpayCertificate();
    
    /**
     * Appid
     */
    public abstract function getAppId();
    
}
