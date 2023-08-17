# fastFace 实人认证

## 简介

## 一、功能列表

1. 阿里云的金融级 App 实人认证（2023-08-17）

`阿里云金融级 App 实人认证` 的文档地址:
> https://help.aliyun.com/zh/id-verification/developer-reference/integration-by-using-the-native-app-sdk-mode-3


## 二、使用方法

### 1、引入
```shell
composer require sxqibo/fast-face
```

### 2、实例化

实例化
```php
$verify = new VerifyFaceForAliyun($config);
```

类名为 VerifyFaceForAliyun, 后续会整合 百度、腾讯 等 SDK

$config 参数说明
```php
$config = [
    // keyId
    'accessKeyId' => 'LTAI5tMbs8UwjPW7wCMpQgFx',
    // keySecret
    'accessKeySecret' => 'pCVFThIoZan8sgXCeTu8IMu64Nbmsg',
    // 场景 id
    'sceneId' => '1000007670',
    // 要接入的方案
    'productCode' => 'ID_PLUS',
    // 要进行活体检测的类型
    'model' => 'MULTI_ACTION',
    // 认证结果的回调通知地址
    'callbackUrl' => '',
    // 加密类型 暂时没有实现
    // 'encryptType' => 'SM2'
];
```
配置可以从 .env 或 数据库 中获得

具体参数详解见阿里云文档，文档地址：
> https://help.aliyun.com/zh/id-verification/developer-reference/initfaceverify-2?spm=a2c4g.11186623.0.0.126c1a12MUmDE1

### 3、调用方法

#### （1）获取 请求凭证 id

方法定义:
```php
public function getCertifyId($initFaceVerify)
```

参数说明：
```php
$initFaceVerifyResponse = $verify->getCertifyId([
    // 订单号，业务代码生成
    'outerOrderNo' => $outerOrderNo,
    // metaInfo，App 端的 SDK 获取
    'metaInfo'     => '',
    // 姓名
    'certName'     => '',
    // 身份证号
    'certNo'       => ''
]);
```

具体参数详解见阿里云文档，文档地址：
> https://help.aliyun.com/zh/id-verification/developer-reference/initfaceverify-2?spm=a2c4g.11186623.0.0.126c1a12MUmDE1

#### （1）获取 认证结果

方法定义:
```php
public function getCertifyResult(string $certifyId, string $sceneId)
```

参数说明：
- $certifyId：通过第一个方法请求得到
- $sceneId：场景 id

### 4、返回结果

具体返回结果参考阿里云文档即可，文档地址：
> https://help.aliyun.com/zh/id-verification/developer-reference/describefaceverify?spm=a2c4g.11186623.0.0.67f7394foD2cK0

## 四、报错处理

若出现错误如下：
Fatal error: Uncaught GuzzleHttp\Exception\RequestException: cURL error 60: SSL certificate problem: unable to get local issuer certificate (see https://curl.haxx.se/libcurl/… in xxx.php

其原因是由于本地的CURL的SSL证书太旧了，导致不识别此证书。

解决方法
1. 从 http://curl.haxx.se/ca/cacert.pem 下载一个最新的证书。然后保存到一个任意目录。
2. 然后把catr.pem放到php的bin目录下，然后编辑php.ini，用记事本或者notepad++打开 php.ini文件，大概在1932行。
   去掉curl.cainfo前面的注释“;”，然后在后面写上cacert.pem证书的完整路径及文件名，我的如下：
3. curl.cainfo = /Applications/EasySrv/software/php/php-8.2/bin/cacert.pem
