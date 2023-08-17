<?php

namespace Sxqibo\FastFace;

use AlibabaCloud\SDK\Cloudauth\V20190307\Cloudauth;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\DescribeFaceVerifyRequest;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\DescribeFaceVerifyResponse;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\InitFaceVerifyRequest;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\InitFaceVerifyResponse;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Exception;

/**
 * 阿里云实人认证
 */
class VerifyFaceForAliyun
{
    /**
     * access_key_id
     *
     * @var string
     */
    public string $accessKeyId;

    /**
     * access_key_secret
     *
     * @var string
     */
    public string $accessKeySecret;

    /**
     * 服务地址列表
     *
     * @var array|string[]
     */
    public array $endpoints = [
        'cloudauth.cn-shanghai.aliyuncs.com',
        'cloudauth.cn-beijing.aliyuncs.com'
    ];

    /**
     * 场景 id
     *
     * @var string|mixed
     */
    public string $sceneId;

    /**
     * 要接入的认证方案。取值：
     *  ID_PRO：使用ID_PRO认证方案，您的用户仅需要做活体认证。
     *  ID_PLUS：使用ID_PLUS认证方案，您的用户需要拍摄身份证和活体认证。
     *
     *  说明 如果您的业务需要同步采集身份证信息，可选择ID_PLUS方案。
     *
     * @var string
     */
    public string $productCode;

    /**
     * 要接入的认证方案的取值
     */
    const ID_PRO = 'ID_PRO';
    const ID_PLUS = 'ID_PLUS';
    const PRODUCT_CODE_VALUE = [self::ID_PRO, self::ID_PLUS];

    /**
     * 要进行活体检测的类型。取值：
     *  LIVENESS（默认）：眨眼动作活体检测。
     *  PHOTINUS_LIVENESS：眨眼动作活体+炫彩活体双重检测。
     *  MULTI_ACTION：多动作活体检测。当前为眨眼+任意摇头检测。
     *
     * @var string
     */
    public string $model;

    /**
     * 要进行活体检测的类型的取值
     */
    const LIVENESS = 'LIVENESS';
    const PHOTINUS_LIVENESS = 'PHOTINUS_LIVENESS';
    const MULTI_ACTION = 'MULTI_ACTION';
    const MODEL_VALUE = [self::LIVENESS, self::PHOTINUS_LIVENESS, self::MULTI_ACTION];

    /**
     * 用户证件类型
     *  唯一取值：IDENTITY_CARD。
     *
     *  说明 当ProductCode为ID_PLUS时，CertType为非必填字段。
     */
    const CERT_TYPE = 'IDENTITY_CARD';

    /**
     * 认证结果的回调通知地址
     *  回调请求方式默认为GET，回调地址必须以https开头。
     *  平台在完成认证后会回调该地址，并自动添加certifyId、passed字段，passed字段值返回的是subcode值，示例：https://www.aliyun.com?callbackToken=1000004826&certifyId=shaxxxx&passed=200。
     *  仅当认证完成（包含认证通过和认证未通过）时才会触发回调，若认证中放弃、异常中断或未进行认证均不会通知。建议您收到回调通知时，若有需要可通过查询接口获取认证详情信息。
     *
     * @var string
     */
    public string $callbackUrl;


    public function __construct(array $config)
    {
        $this->accessKeyId = $config['accessKeyId'];
        $this->accessKeySecret = $config['accessKeySecret'];
        $this->sceneId = $config['sceneId'];
        $this->productCode = $config['productCode'];
        $this->model = $config['model'];
        $this->callbackUrl = $config['callbackUrl'];
    }

    /**
     * 初始化账号 Client
     *
     * @param string $endPoint
     * @return Cloudauth
     */
    private function createClient(string $endPoint): Cloudauth
    {
        $config = new Config([
            'accessKeyId' => $this->accessKeyId,
            'accessKeySecret' => $this->accessKeySecret,
            'endpoint' => $endPoint
        ]);

        return new Cloudauth($config);
    }

    /**
     * 支持服务路由的请求
     *
     * @param  $request
     * @return |null
     */
    public function faceVerifyAutoRoute($request, $method)
    {
        foreach ($this->endpoints as $endpoint) {
            try {
                $response = $this->$method($endpoint, $request);

                if (Utils::equalNumber(500, $response->statusCode)) {
                    continue;
                }

                if(Utils::equalString("500", $response->body->code)){
                    continue;
                }

                return $response;
            } catch (Exception $err) {
                var_dump($err -> getCode());
                var_dump($err -> getMessage());
                throw $err;
            }
        }

        return null;
    }

    /**
     * 发起认证请求，获取请求凭证 id
     *
     * @param string $endpoint
     * @param InitFaceVerifyRequest $request
     * @return InitFaceVerifyResponse
     */
    public function initFaceVerify(string $endpoint, InitFaceVerifyRequest $request): InitFaceVerifyResponse
    {
        $client = $this->createClient($endpoint);

        // 创建RuntimeObject实例并设置运行参数。
        $runtime = new RuntimeOptions([]);
        $runtime->readTimeout = 10000;
        $runtime->connectTimeout = 10000;

        return $client->initFaceVerifyWithOptions($request, $runtime);
    }

    /**
     * describeFaceVerify
     * @param string $endpoint
     * @param DescribeFaceVerifyRequest $request
     * @return DescribeFaceVerifyResponse
     */
    public function describeFaceVerify(string $endpoint, DescribeFaceVerifyRequest $request): DescribeFaceVerifyResponse
    {
        $client = $this->createClient($endpoint);

        // 创建RuntimeObject实例并设置运行参数。
        $runtime = new RuntimeOptions([]);
        $runtime->readTimeout = 10000;
        $runtime->connectTimeout = 10000;

        return $client->describeFaceVerifyWithOptions($request, $runtime);
    }

    /**
     * 生成 InitFaceVerifyRequest
     *
     * @param $initFaceVerify
     * @return InitFaceVerifyRequest
     * @throws Exception
     */
    private function makeFaceVerifyRequest($initFaceVerify): InitFaceVerifyRequest
    {
        if (!in_array($this->productCode, self::PRODUCT_CODE_VALUE)) {
            throw new Exception('ProductCode 取值不正确!');
        }

        if (!in_array($this->model, self::MODEL_VALUE)) {
            throw new Exception('Model 取值不正确!');
        }

        if ($this->productCode == self::ID_PLUS) {
            $initFaceVerify['certType'] = self::CERT_TYPE;

            if (!isset($initFaceVerify['certName']) || !isset($initFaceVerify['certNo'])) {
                throw new Exception('使用 ID_PLUS 认证方案，身份证和真实姓名不能为空');
            }
        }

        if (!empty($this->callbackUrl)) {
            $initFaceVerify['callbackUrl'] = $this->callbackUrl;
        }

        $initFaceVerify['productCode'] = $this->productCode;
        $initFaceVerify['model'] = $this->model;
        $initFaceVerify['sceneId'] = $this->sceneId;

        return new InitFaceVerifyRequest($initFaceVerify);
    }

    /**
     * 发起认证请求
     *
     * @return InitFaceVerifyResponse
     * @throws Exception
     */
    public function getCertifyId($initFaceVerify)
    {
        $response = null;

        try {
            $initFaceVerifyRequest = $this->makeFaceVerifyRequest($initFaceVerify);
            $response = $this->faceVerifyAutoRoute($initFaceVerifyRequest, 'initFaceVerify');
        } catch (Exception $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * 查询 App 实人认证结果
     */
    public function getCertifyResult(string $certifyId)
    {
        $result = null;

        try {
            $request = new DescribeFaceVerifyRequest(['sceneId' => $this->sceneId, 'certifyId' => $certifyId]);
            $result = $this->faceVerifyAutoRoute($request, 'describeFaceVerify');
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }
}
