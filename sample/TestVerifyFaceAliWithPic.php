<?php

use Sxqibo\FastFace\VerifyFaceForAliyun;
use Sxqibo\FastFace\VerifyFaceWithPicForAliyun;

require __DIR__ . '/../vendor/autoload.php';

class TestVerifyFaceAliWithPic
{
    // 配置可以从 .env 或 数据库 中获得
    public $config = [
        // keyId
        'accessKeyId'     => '',
        // keySecret
        'accessKeySecret' => '',
        // 场景 id
        'sceneId'         => '',
        // 要接入的方案
        'productCode'     => 'ID_MIN',
    ];

    // 单号，业务中生成即可
    public $outerOrderNo = "1234567890";

    public function verifyFace()
    {
        $verify = new VerifyFaceWithPicForAliyun($this->config);

        $initFaceVerifyResponse = $verify->verifyFace([
            // 订单号
            'outerOrderNo'           => $this->outerOrderNo,
            // 姓名
            'certName'               => '',
            // 身份证号
            'certNo'                 => '',
            // 人像地址
            'faceContrastPictureUrl' => '',
        ]);

        if ($initFaceVerifyResponse->body->code != 200) {
            print $initFaceVerifyResponse->body->message;
            exit;
        }

        // 打印 请求凭证 id
        $certifyId = $initFaceVerifyResponse->body->resultObject->certifyId;
        print 'certifyId(请求凭证 id):' . $certifyId . PHP_EOL;
        var_dump($initFaceVerifyResponse);
    }
}

$testVerifyFaceAli = new TestVerifyFaceAliWithPic();
// 获取 请求凭证 id
$testVerifyFaceAli->verifyFace();
