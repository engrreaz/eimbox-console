<?php
/**
 * EIMBox Gateway Manager & Factory
 * Dynamically resolves and executes the appropriate SMS gateway driver.
 */

require_once __DIR__ . '/SmsGatewayInterface.php';
require_once __DIR__ . '/SandboxGateway.php';
require_once __DIR__ . '/BulkSmsBdGateway.php';
require_once __DIR__ . '/SmsVaultsGateway.php';
require_once __DIR__ . '/GreenwebGateway.php';
require_once __DIR__ . '/SslWirelessGateway.php';
require_once __DIR__ . '/GenericGateway.php';

class GatewayManager
{
    /**
     * Resolves the gateway driver instance based on configuration
     *
     * @param array $config
     * @return SmsGatewayInterface
     */
    public static function resolve(array $config): SmsGatewayInterface
    {
        // 1. Sandbox / Simulation Mode Check (Zero Cost)
        if (!empty($config['sandbox_mode']) && intval($config['sandbox_mode']) === 1) {
            return new SandboxGateway();
        }

        $provider = strtolower(trim($config['provider'] ?? ''));
        $uri = strtolower(trim($config['uri'] ?? ''));

        // 2. Explicit Provider Match or URI Auto-detection
        if ($provider === 'bulksmsbd' || str_contains($uri, 'bulksmsbd.net')) {
            return new BulkSmsBdGateway();
        }

        if ($provider === 'smsvaults' || str_contains($uri, 'smsvaults.work')) {
            return new SmsVaultsGateway();
        }

        if ($provider === 'greenweb' || str_contains($uri, 'greenweb.com.bd')) {
            return new GreenwebGateway();
        }

        if ($provider === 'sslwireless' || str_contains($uri, 'sslwireless.com')) {
            return new SslWirelessGateway();
        }

        // 3. Fallback to Generic Tag Gateway
        return new GenericGateway();
    }

    /**
     * High-level dispatch helper with error handling and fallback
     *
     * @param array $config
     * @param string $mobile
     * @param string $message
     * @return array
     */
    public static function dispatch(array $config, string $mobile, string $message): array
    {
        if (empty($config['uri']) && empty($config['sandbox_mode'])) {
            return [
                'status'          => 'failed',
                'response_code'   => 400,
                'message_id'      => '',
                'success_message' => '',
                'error_message'   => 'SMS Gateway URL is not configured'
            ];
        }

        try {
            $gateway = self::resolve($config);
            return $gateway->send($config, $mobile, $message);
        } catch (Throwable $e) {
            return [
                'status'          => 'failed',
                'response_code'   => 500,
                'message_id'      => '',
                'success_message' => '',
                'error_message'   => 'Gateway Exception: ' . $e->getMessage()
            ];
        }
    }
}
