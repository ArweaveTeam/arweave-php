<?php

namespace Arweave\SDK\Support;

use Arweave\SDK\Support\Helpers;
use Arweave\SDK\Support\Wallet;
use Exception;

class Transaction
{
    /**
     * Transaction attributes
     *
     * @var string[]
     */
    protected $attributes = [
        'id'        => null,
        'last_tx'   => null,
        'owner'     => null,
        'target'    => null,
        'quantity'  => null,
        'type'      => null,
        'data'      => null,
        'reward'    => null,
        'signature' => null,
        'tags'      => [],
    ];

    /**
     * @param string[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        if (!array_key_exists('id', $attributes)) {
            $this->attributes['id'] = Helpers::base64urlEncode(base64_encode(random_bytes(32)));
        }

        // All fields must be strings, so cast them all
        $this->attributes = array_map(function ($attribute) {
            return is_array($attribute) ? $attribute : (string) $attribute;
        }, $this->attributes);
    }

    /**
     * Get the transaction attributes as an array.
     *
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute.
     *
     * @param  string $attribute Attribute key
     *
     * @return mixed
     */
    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    /**
     * Sign the transaction and return the signature.
     *
     * @param  \Arweave\SDK\Support\Wallet $wallet
     *
     * @return string
     *
     * concatenate_buffers([owner, target, id, data, quantity, reward, last]);
     *
     * owner, target, id
     */
    public function sign(Wallet $wallet): string
    {
        $message = $this->getMessage();

        $signature = $wallet->sign($message);

        $this->attributes['signature'] = Helpers::base64urlEncode(base64_encode($signature));

        if (!$wallet->verify($message, $signature)) {
            throw new Exception('Arweave Transaction - Error validating transaction signature.');
        }

        return $signature;
    }

    /**
     * Get the transaction message to sign.
     *
     * @return string
     */
    private function getMessage(): string
    {
        return base64_decode(Helpers::base64urlDecode($this->attributes['owner'])) .
        base64_decode(Helpers::base64urlDecode($this->attributes['target'])) .
        base64_decode(Helpers::base64urlDecode($this->attributes['id'])) .
        base64_decode(Helpers::base64urlDecode($this->attributes['data'])) .
        $this->attributes['quantity'] .
        $this->attributes['reward'] .
        base64_decode(Helpers::base64urlDecode($this->attributes['last_tx']));
    }
}
