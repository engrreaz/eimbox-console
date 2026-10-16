<?php
require_once __DIR__ . '/SmsGatewayInterface.php';

/**
 * SMSVaults Gateway Driver (cpanel.smsvaults.work)
 */
class SmsVaultsGateway implements SmsGatewayInterface
{
    public function send(array $config, string $mobile, string $message): array
    {
        $apiKey = $config['api_key'] ?? '';
        $secretKey = $config['secret_key'] ?? '';
        $callerId = $config['username'] ?? '01234567890';
        $encodedMsg = urlencode($message);

        $baseUrl = !empty($config['uri']) ? $config['uri'] : 'http://cpanel.smsvaults.work/sendtext';
        if (!str_contains($baseUrl, '?')) {
            $url = "{$baseUrl}?apikey={$apiKey}&secretkey={$secretKey}&callerID={$callerId}&toUser={$mobile}&messageContent={$encodedMsg}";
        } else {
            $url = str_replace(
                ['{apikey}', '{secretkey}', '{senderid}', '{mobile}', '{message}'],
                [$apiKey, $secretKey, $callerId, $mobile, $encodedMsg],
                $baseUrl
            );
        }

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

        $response = json_decode($response_raw, true);
        $statusVal = $response['Status'] ?? '';
        $messageId = $response['Message_ID'] ?? '';
        $textMsg = $response['Text'] ?? '';

        $isSuccess = ($statusVal === '0' || strtolower((string)$statusVal) === 'success' || !empty($messageId));

        return [
            'status'          => $isSuccess ? 'success' : 'failed',
            'response_code'   => 200,
            'message_id'      => (string)$messageId,
            'success_message' => $isSuccess ? ($textMsg ?: 'SMS dispatched successfully via SMSVaults') : '',
            'error_message'   => $isSuccess ? '' : ($textMsg ?: $response_raw)
        ];
    }
}
