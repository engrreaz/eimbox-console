<?php
require_once __DIR__ . '/SmsGatewayInterface.php';

/**
 * Greenweb Bangladesh Gateway Driver (greenweb.com.bd)
 */
class GreenwebGateway implements SmsGatewayInterface
{
    public function send(array $config, string $mobile, string $message): array
    {
        $token = $config['api_key'] ?? '';
        $url = !empty($config['uri']) ? $config['uri'] : 'http://api.greenweb.com.bd/api.php';

        $data = [
            'token'   => $token,
            'to'      => $mobile,
            'message' => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response_raw = curl_exec($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($curl_err) {
            return [
                'status'          => 'failed',
                'response_code'   => 500,
                'message_id'      => '',
                'success_message' => '',
                'error_message'   => 'cURL Error: ' . $curl_err
            ];
        }

        $isSuccess = str_contains((string)$response_raw, 'Ok:') || str_contains((string)$response_raw, 'Success');

        return [
            'status'          => $isSuccess ? 'success' : 'failed',
            'response_code'   => 200,
            'message_id'      => $isSuccess ? 'GW_' . uniqid() : '',
            'success_message' => $isSuccess ? (string)$response_raw : '',
            'error_message'   => $isSuccess ? '' : (string)$response_raw
        ];
    }
}
