<?php
require_once __DIR__ . '/SmsGatewayInterface.php';

/**
 * EIMBox Zero-Cost Sandbox Simulation Gateway
 * Simulates real SMS dispatch without deducting any balance or hitting external APIs.
 */
class SandboxGateway implements SmsGatewayInterface
{
    public function send(array $config, string $mobile, string $message): array
    {
        return [
            'status'          => 'success',
            'response_code'   => 200,
            'message_id'      => 'SIM_' . uniqid(),
            'success_message' => 'Sandbox Simulated: SMS sent successfully (No balance deducted)',
            'error_message'   => ''
        ];
    }
}
