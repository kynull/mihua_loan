<?php
namespace llpay;

require_once('security.function.php');
class Base
{
    var $llpay_config;
    public function __construct()
    {
        $this->llpay_config = $this->getConfig();
    }

    /**
     * 返回格式化参数返回带req_data字段的HTML表单
     * @param $url 表单提交地址
     * @param $data 参数数组
     * @return string 带req_data字段的HTML表单
     */
    protected function Form_Post($url,$data)
    {
        $method = 'post';
        $button_name = '提交申请';

        // 建立请求
        $llpaySubmit = new Submit($this->llpay_config);

        $result = $llpaySubmit->buildRequestPara($data);

        $resultJsonEncode = json_encode($result);

        $sHtml = "<form id='llpaysubmit' name='llpaysubmit' action='" . $url . "' method='" . $method . "'>";
        $sHtml .= "<input type='hidden' name='req_data' value='" . $resultJsonEncode . "'/>";
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='" . $button_name . "' style='display: none;'></form>";
        $sHtml = $sHtml . "<script>document.forms['llpaysubmit'].submit();</script>";
        return $sHtml;
    }
    protected function Https_Post($url,$data) {

        $llpaySubmit = new Submit($this->llpay_config);
        $jsonResult = $llpaySubmit->buildRequestHttps($data,$url);

        return $jsonResult;
    }
    protected function Http_Post($url,$data) {
        $llpaySubmit = new Submit($this->llpay_config);
        $jsonResult = $llpaySubmit->buildRequestHttp($data,$url);

        return $jsonResult;
    }
    protected function API_Post($url,$data,$isEncrypt = true)
    {
        $html_text = '';
        // 建立请求
        $llpaySubmit = new API_Submit($this->llpay_config);
        // 对参数排序加签名
        $sortPara = $llpaySubmit->buildRequestPara($data);
        // 传json字符串
        $json = json_encode($sortPara);
        //echo('API_Post请求->json源串：'. $json . '\r\n');
        if ($isEncrypt) {
            $parameterRequest = array(
                "oid_partner" => trim($this->llpay_config['oid_partner']),
                "pay_load" => ll_encrypt($json, $this->llpay_config['LIANLIAN_PUBLICK_KEY']) // 请求参数加密
            );
            $html_text = $llpaySubmit->buildRequestJSON($parameterRequest,$url);
        } else {
            echo('API_Post请求->json源串：'. $json . '\r\n');
            $html_text = $llpaySubmit->buildRequestJSON($json,$url);
        }

        return $html_text;
    }
    private function getConfig()
    {
        // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ 请在这里配置您的基本信息 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        // 商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
        $llpay_config['oid_partner'] = '201705031001695515';  // 正式商户号：201705031001695515

        // 秘钥格式注意不能修改（左对齐，右边有回车符）  商户私钥，通过openssl工具生成,私钥需要商户自己生成替换，对应的公钥通过商户站上传
        $llpay_config['RSA_PRIVATE_KEY'] = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQC1SP40rhCNLewxoq4M9WDYgrv0sp2QSJcydF4xdKWOn+6aQ0W+
VGRx4TUAW/bJKFeqT8izKrfutNqV/dvYKY6znlHzIPfB3NVXWA1Xna2Mh7jSXvQ1
xFocgcuKXLntc/9J89GYhXseZ3VMd9qbhXphbHjWhOZoiBJErCsOqgIIpQIDAQAB
AoGADFhnL1E3QVYCQrMQZUOEj9n/UL+lKUE4QrBYBcgqyhasGgdAxaBbosGyaU7Z
9ILxzWBXi5P6KKBaTmKWSRczZ3x8UfG1SURoFLgI41fY+vHE/VTt0tD39M9uraDr
AjQtYmTqO2ePBqX5OUe4Tqy/MLwXCXg8Wk6muSX9Pmo3TFUCQQDvbMqZHgLVhjDG
70sw1nIHif2HAq17Ejqev94eCLf0q20c/juzpX/2ZVFFmNT18kqKB3CfSIfDx8Jz
stZEv4RXAkEAwdXRg1z9DYqgVixd8xpI3MQ/8Lbd/qr06IYxABNJ2YWeaetg2M7H
+H95vKJZHRrnmnf2X1iOKtmTyhXhlSwdYwJAaK1q4ojhelNiDgffGuoXDr7A7n84
M7+ji3adeQocy0cLvIpWtdNc3/AqGUCZkzIsbq6UCb/fQ7SZipYc4g/NnwJAMopN
1rKoSJ+crttio8B2vxlskpcbywtIUFis7hgZaV4tr/BvkBhai7CxTT4Hfk9FlEEz
PCka6JFSt5588yhNgQJBAOMBYcPLP8igPqoE9qEGBToPqycfK1X55eXKJH925JzG
dnfnfh4vU/Q2GCIaZCvViSXF6ZAo7YIlDWEdTQaFd/Y=
-----END RSA PRIVATE KEY-----';
        // file_get_contents('key/rsa_private_key.pem');
        // file_get_contents('key/rsa_public_key.pem');

        // 连连银通公钥
        $llpay_config['LIANLIAN_PUBLICK_KEY'] = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCSS/DiwdCf/aZsxxcacDnooGph3d2JOj5GXWi+
q3gznZauZjkNP8SKl3J2liP0O6rU/Y/29+IUe+GTMhMOFJuZm1htAtKiu5ekW0GlBMWxf4FPkYlQ
kPE0FtaoMP3gYfh+OwI+fIRrpW3ySn3mScnc6Z700nU/VYrRkfcSCbSnRwIDAQAB
-----END PUBLIC KEY-----';

        $llpay_config['cacert'] = '';

        // 安全检验码，以数字和字母组成的字符
        $llpay_config['key'] = '201608101001022519_test_20160810';
        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ 请在这里配置您的基本信息 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        // 版本号
        $llpay_config['version'] = '1.2';

        // 请求应用标识 为wap版本，不需修改
        $llpay_config['app_request'] = '3';

        // 签名方式 不需修改
        $llpay_config['sign_type'] = strtoupper('RSA');

        // 订单有效时间  分钟为单位，默认为10080分钟（7天）
        $llpay_config['valid_order'] ="10080";

        // 字符编码格式 目前支持 gbk 或 utf-8
        $llpay_config['input_charset'] = strtolower('utf-8');

        // 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $llpay_config['transport'] = 'https';

        $llpay_config['cacert'] = __DIR__. '/key/wechat.cloudpayfs.com.pem';
        return $llpay_config;
    }
}