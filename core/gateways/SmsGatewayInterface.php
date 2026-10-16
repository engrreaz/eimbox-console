<?php
/**
 * EIMBox SMS Gateway Interface
 * Standard contract for all SMS providers
 */
interface SmsGatewayInterface
{
    /**
     * Dispatch an SMS message to recipient
     * 
     * @param array $config Gateway settings array (api_key, secret_key, username, password, uri, etc.)
     * @param string $mobile Recipient 11-digit BD mobile number
     * @param string $message Final formatted message content
     * @return array Standardized response:
     *               [
     *                  'status'          => 'success' | 'failed',
     *                  'response_code'   => int|string,
     *                  'message_id'      => string,
     *                  'success_message' => string,
     *                  'error_message'   => string
     *               ]
     */
    public function send(array $config, string $mobile, string $message): array;
}
