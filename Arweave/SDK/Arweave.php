<?php

namespace Arweave\SDK;

use Exception;
use Arweave\SDK\Support\API;
use Arweave\SDK\Support\Wallet;
use Arweave\SDK\Support\Helpers;
use Arweave\SDK\Support\Transaction;

class Arweave
{

    /**
     * Arweave API object.
     *
     * @var \Arweave\SDK\Support\API
     */
    protected $api;

    /**
     * Arweave node hostname or IP address
     *
     * @var string
     */
    protected $host;

    /**
     * @param string $protocol 'http' or 'https'
     * @param string $host IP address or hostname
     * @param int $port Port number
     */
    public function __construct($protocol, $host, $port)
    {
        $this->api = new API($protocol, $host, $port);
    }

    public function api(): APi {
        return $this->api;
    }

    /**
     * Create a new transaction object from a given wallet and piece of data.
     *
     * @param  Wallet $wallet Sending wallet
     *
     * @param  string $data Data to be added to the transaction
     *
     * @return \Arweave\SDK\Support\Transaction
     */
    public function createTransaction(Wallet $wallet, $attributes): Transaction
    {

        if (!$attributes || !is_array($attributes)) {
            throw new Exception('Invalid transaction attributes passed');
        }

        $encoded_tags = array_map(function($name) use ($attributes){
            return [
                'name' => Helpers::base64urlEncode(base64_encode($name)),
                'value' => Helpers::base64urlEncode(base64_encode($attributes['tags'][$name]))
            ];
        }, array_keys($attributes['tags'] ?? []));

        $transaction = new Transaction([
            'last_tx'  => $this->api->getLastTransaction($wallet->getAddress()),
            'owner'    => $wallet->getOwner(),
            'tags'     => $encoded_tags,
            'target'   => $attributes['target'] ?? '',
            'quantity' => $attributes['quantity'] ?? '0',
            'data'     => Helpers::base64urlEncode(base64_encode($attributes['data'] ?? '')),
            'reward'   => $attributes['reward'] ?? 
                $this->api->getReward(
                    strlen($attributes['data'] ?? ''),
                    $attributes['target'] ?? null
                ),
        ]);

        $transaction->sign($wallet);

        return $transaction;
    }
}
