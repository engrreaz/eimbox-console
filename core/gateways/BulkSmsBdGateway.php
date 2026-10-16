<?php
require_once __DIR__ . '/SmsGatewayInterface.php';

/**
 * BulkSMSBD Gateway Driver (bulksmsbd.net)
 */
class BulkSmsBdGateway implements SmsGatewayInterface
{
    public function send(array $config, string $mobile, string $message): array
    {
        $apiKey = $config['api_key'] ?? '';
        $senderId = $config['username'] ?? '';
        $url = !empty($config['uri']) ? $config['uri'] : 'http://bulksmsbd.net/api/smsapi';

        $data = [
            'api_key'  => $apiKey,
            'senderid' => $senderId,
            'number'   => $mobile,
            'message'  => $message
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

        $response = json_decode($response_raw, true);
        $responseCode = $response['response_code'] ?? '';
        $messageId = $response['message_id'] ?? '';
        $successMessage = $response['success_message'] ?? '';
        $errorMessage = $response['error_message'] ?? '';

        $isSuccess = ($responseCode == 202 || $responseCode == 200 || !empty($messageId));

        return [
            'status'          => $isSuccess ? 'success' : 'failed',
            'response_code'   => $responseCode ?: ($isSuccess ? 200 : 400),
            'message_id'      => (string)$messageId,
            'success_message' => $isSuccess ? ($successMessage ?: 'SMS dispatched successfully via BulkSMSBD') : '',
            'error_message'   => $isSuccess ? '' : ($errorMessage ?: $response_raw)
        ];
    }
}
