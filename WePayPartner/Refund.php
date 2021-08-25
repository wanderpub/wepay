<?php

namespace WePayPartner;

use WePay\Basic\Tools;
use WePay\Exceptions\InvalidDecryptException;
use WePay\Exceptions\InvalidResponseException;
use WePay\Basic\BasicWePay;

/**
 * 订单退款接口
 * Class Refund
 * @package WePartnerPay
 */
class Refund extends BasicWePay
{
    /**
     * 服务商模式创建退款订单
     * @param array $data 退款参数
     * @return array
     * @throws InvalidResponseException
     */
    public function create($data)
    {
        return $this->doRequest('POST', '/v3/refund/domestic/refunds', json_encode($data, JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 退款订单查询
     * @param string $refundNo 退款单号
     * @return array
     * @throws InvalidResponseException
     */
    public function query($refundNo)
    {
        $pathinfo = "/v3/refund/domestic/refunds/{$refundNo}";
        return $this->doRequest('GET', "{$pathinfo}?sub_mchid={$this->config['sub_mchid']}", '', true);
    }

    /**
     * 获取退款通知
     * @return array
     * @throws InvalidDecryptException
     * @throws InvalidResponseException
     */
    public function notify()
    {
        $data = Tools::xml2arr(file_get_contents("php://input"));
        if (!isset($data['return_code']) || $data['return_code'] !== 'SUCCESS') {
            throw new InvalidResponseException('获取退款通知XML失败！');
        }
        try {
            $key = md5($this->config['mch_v3_key']);
            $decrypt = base64_decode($data['req_info']);
            $response = openssl_decrypt($decrypt, 'aes-256-ecb', $key, OPENSSL_RAW_DATA);
            $data['result'] = Tools::xml2arr($response);
            return $data;
        } catch (\Exception $exception) {
            throw new InvalidDecryptException($exception->getMessage(), $exception->getCode());
        }
    }
}
