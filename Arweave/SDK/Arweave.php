<?php

namespace Arweave\SDK;

use Arweave\SDK\Support\API;
use Arweave\SDK\Support\Helpers;
use Arweave\SDK\Support\Transaction;
use Arweave\SDK\Support\Wallet;

class Arweave
{

    /**
     * Arweave API object.
     *
     * @var \Arweave\SDK\Support\API
     */
    protected $api;

    /**
     * Network node IP
     *
     * @var string
     */
    protected $ip = '139.59.81.47';

    /**
     * @param string $ip
     */
    public function __construct($ip)
    {
        $this->api = new API($ip);
    }

    /**
     * Commit a transaction to the blockweave.
     *
     * @param  \Arweave\SDK\Support\Transaction $transaction
     *
     * @return \Arweave\SDK\Support\Transaction
     */
    public function commit(Transaction $transaction)
    {
        return $this->api->commit($transaction);
    }

    /**
     * Get a transation by transaction ID.
     *
     * @param  string $transaction_id
     *
     * @return mixed
     */
    public function getTransaction(string $transaction_id)
    {
        return $this->api->getTransaction($transaction_id);
    }

    /**
     * Get the data only from a transation by transaction ID.
     *
     * @param  string $transaction_id
     *
     * @return mixed
     */
    public function getData(string $transaction_id)
    {
        return $this->api->getData($transaction_id);
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
    public function createTransaction(Wallet $wallet, string $data): Transaction
    {
        $transaction = new Transaction([
            'last_tx'  => $this->api->lastTransaction($wallet->getAddress()),
            'owner'    => $wallet->getAddress(),
            'quantity' => 0,
            'type'     => 'data',
            'data'     => Helpers::base64urlEncode(base64_encode($data)),
            'reward'   => $this->api->getReward(strlen($data) + 2000),
        ]);

        $transaction->sign($wallet);

        return $transaction;
    }

}
