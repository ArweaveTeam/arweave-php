<?php

namespace Arweave\SDK\Support;

use Exception;
use Jose\KeyConverter\RSAKey;
use phpseclib\Crypt\RSA;

class Wallet
{
    const HASH = 'sha256';

    /**
     * Public key
     *
     * @var \phpseclib\Crypt\RSA
     */
    private $public;

    /**
     * Private key
     *
     * @var \phpseclib\Crypt\RSA
     */
    private $private;

    /**
     * @param array $jwk JWK as an array
     */
    public function __construct(array $jwk = [])
    {
        if (!$jwk) {
            throw new Exception('No key file specified');
        }

        $this->public  = $this->getRSAFromJDK($jwk);
        $this->private = $this->getRSAFromJDK($jwk, true);
        $this->address = $jwk['n'];
    }

    /**
     * Get the wallet address.
     *
     * @return string Wallet address base64url encoded
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Sign the given message.
     *
     * @param  string $message Message to sign
     *
     * @return string Message signature
     */
    public function sign($message): string
    {
        return $this->private->sign($message);
    }

    /**
     * Verify the given mesage using the current wallet public key.
     *
     * @param  string $message   Original message
     * @param  string $signature Signature to verify
     *
     * @return bool
     */
    public function verify(string $message, string $signature): bool
    {
        return $this->public->verify($message, $signature);
    }

    /**
     * Get a phpseclib with the appropriate key loaded.
     *
     * By default the public key will be loaded, if $get_private_key = true
     * then the private key will be loaded.
     *
     * @param  boolean $get_private_key Use the private key instead of the public key?
     *
     * @return \phpseclib\Crypt\RSA
     */
    private function getRSAFromJDK(array $jwk, $get_private_key = false): RSA
    {
        if (!$get_private_key) {
            unset($jwk['d']);
        }

        $rsa = new RSA;
        $rsa->setSignatureMode(RSA::SIGNATURE_PSS);
        $rsa->setSaltLength(0);
        $rsa->setHash(static::HASH);
        $rsa->setMGFHash(static::HASH);

        if (!$rsa->loadKey((new RSAKey($jwk))->toPEM())) {
            throw new Exception('Arweave wallet: key could not be loaded');
        }

        return $rsa;
    }

}
