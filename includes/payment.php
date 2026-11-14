<?php
/**
 * PAYMENT GATEWAY HOOK
 * --------------------
 * Right now this is a DEMO: it approves every online payment so you can test the full flow.
 * Before going live, replace the body of process_gateway() with a real integration
 * (JazzCash / Easypaisa / PayFast / Stripe ...). You need merchant credentials from the provider.
 *
 * IMPORTANT: never collect or store raw card numbers on this server. For cards, redirect the
 * customer to the gateway's hosted payment page and confirm the result with their callback/webhook.
 */
function process_gateway(string $method, float $amount, array $details = []): array {
    // TODO: call the real gateway here and return ['ok'=>false,'error'=>'...'] on failure.
    return ['ok' => true, 'ref' => 'DEMO-' . strtoupper(bin2hex(random_bytes(4)))];
}
