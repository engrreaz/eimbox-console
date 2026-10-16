<?php
require_once __DIR__ . '/SmsGatewayInterface.php';

/**
 * Generic SMS Gateway Driver
 * Supports custom GET/POST API endpoints with placeholder tags:
 * {apikey}, {secretkey}, {senderid}, {mobile}, {message}
 */
class GenericGateway implements SmsGatewayInterface
{
    public function send(array $config, string $mobile, string $message): array
    {
        $apiKey = $config['api_key'] ?? '';
        $secretKey = $config['secret_key'] ?? '';
        $senderId = $config['username'] ?? '';
        $urlPattern = $config['uri'] ?? '';

        if (empty($urlPattern)) {
            return [
                'status'          => 'failed',
                'response_code'   => 400,
                'message_id'      => '',
                'success_message' => '',
                'error_message'   => 'SMS Gateway URL is not configured'
            ];
        }

        $encodedMsg = urlencode($message);
        $url = str_replace(
            ['{apikey}', '{secretkey}', '{senderid}', '{mobile}', '{message}'],
            [$apiKey, $secretKey, $senderId, $mobile, $encodedMsg],
            $urlPattern
        );

        $ch = curl_init($url);
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

        return [
            'status'          => 'success',
            'response_code'   => 200,
            'message_id'      => 'GEN_' . uniqid(),
            'success_message' => substr((string)$response_raw, 0, 150),
            'error_message'   => ''
        ];
    }
}
