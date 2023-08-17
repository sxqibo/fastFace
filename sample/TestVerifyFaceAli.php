<?php

use Sxqibo\FastFace\VerifyFaceForAliyun;

require __DIR__ . '/../vendor/autoload.php';

class TestVerifyFaceAli
{
    // 配置可以从 .env 或 数据库 中获得
    public $config = [
        // keyId
        'accessKeyId' => '',
        // keySecret
        'accessKeySecret' => '',
        // 场景 id
        'sceneId' => '',
        // 要接入的方案
        'productCode' => 'ID_PLUS',
        // 要进行活体检测的类型
        'model' => 'MULTI_ACTION',
        // 认证结果的回调通知地址
        'callbackUrl' => '',
        // 加密类型 暂时没有实现
        // 'encryptType' => 'SM2'
    ];

    // 单号，业务中生成即可
    public $outerOrderNo = "1234567890";

    public function getCertifyId()
    {
        $verify = new VerifyFaceForAliyun($this->config);

        $initFaceVerifyResponse = $verify->getCertifyId([
            // 订单号
            'outerOrderNo' => $this->outerOrderNo,
            // metaInfo
            'metaInfo'     => '',
            // 姓名
            'certName'     => '',
            // 身份证号
            'certNo'       => ''
        ]);

        if ($initFaceVerifyResponse->body->code != 200) {
            print $initFaceVerifyResponse->body->message;
            exit;
        }

        // 打印 请求凭证 id
        $certifyId = $initFaceVerifyResponse->body->resultObject->certifyId;
        print 'certifyId(请求凭证 id):' . $certifyId . PHP_EOL;
    }

    public function getVerifyResult()
    {
        // 要查询的凭证 id
        // 回调时会得到该 $certifyId，通过该值去请求验证结果
        $certifyId = '';

        $verify1 = new VerifyFaceForAliyun($this->config);
        $response = $verify1->getVerifyResult($certifyId);

        if ($response->body->code != 200) {
            print $response->body->message;
            exit;
        }

        var_dump($response);
        print 'passed 是否通过认证:' . $response->body->resultObject->passed . PHP_EOL;
    }
}

$testVerifyFaceAli = new TestVerifyFaceAli();
// 获取 请求凭证 id
$testVerifyFaceAli->getCertifyId();
// 获取返回结果
$testVerifyFaceAli->getVerifyResult();
