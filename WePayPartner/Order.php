<?php

namespace WePayPartner;

use WePay\Basic\Tools;
use WePay\Exceptions\InvalidArgumentException;
use WePay\Exceptions\InvalidDecryptException;
use WePay\Exceptions\InvalidResponseException;
use WePayPartner\Basic\BasicWePay;
use WePay\Basic\DecryptAes;

/**
 * 订单支付接口
 * Class Order
 * @package WePartnerPay
 */
class Order extends BasicWePay
{
    const WXPAY_H5 = 'h5';
    const WXPAY_APP = 'app';
    const WXPAY_JSAPI = 'jsapi';
    const WXPAY_NATIVE = 'native';

    /**
     * 创建支付订单
     * @param string $type 支付类型
     * @param array $data 支付参数
     * @return array
     * @throws InvalidResponseException
     */
    public function create($type, $data)
    {
        $types = [
            'h5'     => '/v3/pay/partner/transactions/h5',
            'app'    => '/v3/pay/partner/transactions/app',
            'jsapi'  => '/v3/pay/partner/transactions/jsapi',
            'native' => '/v3/pay/partner/transactions/native',
        ];
        if (empty($types[$type])) {
            throw new InvalidArgumentException("Payment {$type} not defined.");
        } else {
            // 创建预支付码
            $result = $this->doRequest('POST', $types[$type], json_encode($data, JSON_UNESCAPED_UNICODE), true);
            if (empty($result['prepay_id'])) return $result;
            // 支付参数签名
            $time = (string)time();
            $appid = $this->config['sub_appid'];
            $prepayId = $result['prepay_id'];
            $nonceStr = Tools::createNoncestr();
            if ($type === 'app') {
                $sign = $this->signBuild(join("\n", [$appid, $time, $nonceStr, $prepayId, '']));
                return ['appid' => $appid, 'partnerid' => $this->config['sub_mchid'], 'prepayid' => $prepayId, 'package' => 'Sign=WXPay', 'noncestr' => $nonceStr, 'timestamp' => $time, 'sign' => $sign];
            } elseif ($type === 'jsapi') {
                $package = sprintf('prepay_id=%s', $prepayId);
                $sign = $this->signBuild(join("\n", [$appid, $time, $nonceStr, $package, '']));
                return ['appId' => $appid, 'timestamp' => $time, 'nonceStr' => $nonceStr, 'package' => $package, 'signType' => 'RSA', 'paySign' => $sign];
            } else {
                return $result;
            }
        }
    }

    /**
     * 支付订单查询
     * @param string $orderNo 订单单号
     * @return array
     * @throws InvalidResponseException
     */
    public function query($orderNo)
    {
        $pathinfo = "/v3/pay/partner/transactions/id/{$orderNo}";
        // return $this->doRequest('GET', "{$pathinfo}?mchid={$this->config['mch_id']}", '', true);
        $url = sprintf('%s?sub_mchid=&sp_mchid=%s', $pathinfo, $this->config['sub_mchid'], $this->config['sp_mchid']);
        return $this->doRequest('GET', $url, '', true);
    }

    /**
     * 支付通知
     * @return array
     * @throws InvalidDecryptException
     */
    public function notify()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (isset($data['resource'])) {
            $aes = new DecryptAes($this->config['mch_v3_key']);
            $result = $aes->decryptToString(
                $data['resource']['associated_data'],
                $data['resource']['nonce'],
                $data['resource']['ciphertext']
            );
            $result = json_decode($result, true);
            $data['result'] = $result;
        }
        return $data;
    }
}
