<?php

namespace WePay\Basic;

use WePay\Exceptions\InvalidResponseException;
use WePay\Basic\BasicWePay;
use WePay\Basic\DecryptAes;

/**
 * 平台证书管理
 * Class Cert
 * @package WePay
 */
class Cert extends BasicWePay
{

    /**
     * 商户平台下载证书
     * @throws InvalidResponseException
     */
    public function download()
    {
        try {
            $aes = new DecryptAes($this->config['mch_v3_key']);
            $result = $this->doRequest('GET', '/v3/certificates');
            foreach ($result['data'] as $vo) {
                $this->tmpFile($vo['serial_no'], $aes->decryptToString(
                    $vo['encrypt_certificate']['associated_data'],
                    $vo['encrypt_certificate']['nonce'],
                    $vo['encrypt_certificate']['ciphertext']
                ));
            }
        } catch (\Exception $exception) {
            throw new InvalidResponseException($exception->getMessage(), $exception->getCode());
        }
    }
}
