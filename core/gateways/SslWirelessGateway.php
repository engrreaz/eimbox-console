<?php
require_once __DIR__ . '/SmsGatewayInterface.php';

/**
 * SSL Wireless Gateway Driver (smsplus.sslwireless.com)
 */
class SslWirelessGateway implements SmsGatewayInterface
{
    public function send(array $config, string $mobile, string $message): array
    {
        $apiToken = $config['api_key'] ?? '';
        $sid = $config['username'] ?? '';
        $url = !empty($config['uri']) ? $config['uri'] : 'https://smsplus.sslwireless.com/api/v3/send-sms';
        $csmsId = uniqid('SSL_');

        $payload = [
            'api_token' => $apiToken,
            'sid'       => $sid,
            'msisdn'    => $mobile,
            'sms'       => $message,
            'csms_id'   => $csmsId
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
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

        $response = json_decode($response_raw, true);
        $statusVal = strtolower((string)($response['status'] ?? ''));
        $statusCode = intval($response['status_code'] ?? 0);
        $isSuccess = ($statusVal === 'success' || $statusCode == 200);

        return [
            'status'          => $isSuccess ? 'success' : 'failed',
            'response_code'   => $statusCode ?: ($isSuccess ? 200 : 400),
            'message_id'      => (string)($response['smsinfo'][0]['sms_status'] ?? $csmsId),
            'success_message' => $isSuccess ? ($response['status_message'] ?? 'Dispatched via SSL Wireless') : '',
            'error_message'   => $isSuccess ? '' : ($response['error_message'] ?? $response_raw)
        ];
    }
}
