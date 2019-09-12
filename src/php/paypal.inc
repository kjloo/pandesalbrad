<?php

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

class PayPalClient
{
    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     */
    public static function environment()
    {
        $clientId = getenv("CLIENT_ID") ?: "AYCTlwOZjiBLNKvMt_l9T76fEctPt2w_cu6b_Og6r11nX0loNmKH7HO9axw4z2-RuYIGTdvHUJBtaxTy";
        $clientSecret = getenv("CLIENT_SECRET") ?: "EAztQgmb7tyqgFndxy36Jg0DLlZE9iVNSAEbrQH0KV_ftyR-PUcEscTyWesuOPMHQKPtCXtbb-e8ZU7f";
        return new SandboxEnvironment($clientId, $clientSecret);
    }
}