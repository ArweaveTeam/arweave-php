<?php

namespace Arweave\SDK\Support;

use Arweave\SDK\Support\Transaction;
use Exception;

class API
{
    /**
     * Node IP to connect to.
     *
     * @var string
     */
    private $ip;

    /**
     * Network port to use.
     *
     * @var string
     */
    private $port;

    /**
     * Curl handle. Should be reset before each usage.
     *
     * @var resource
     */
    private $handle;

    /**
     * @param string $ip
     * @param string $port
     */
    public function __construct($ip, $port = '1984')
    {
        if (!$ip) {
            throw new Exception('No IP specified');
        }

        $this->ip   = $ip;
        $this->port = $port;

        $this->handle = curl_init();
    }

    /**
     * Commit a transaction to the network.
     *
     * @param  Transaction $transaction
     */
    public function commit(Transaction $transaction)
    {
        $response = $this->post('tx', $transaction->getAttributes());

        if ($response != 'OK') {
            throw new Exception($response);
        }
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
        return $this->get(sprintf('tx/%s', $transaction_id));
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
        return $this->get(sprintf('tx/%s/data', $transaction_id));
    }

    /**
     * Get the last transaction ID for the given wallet address.
     *
     * @param  string $wallet_address
     *
     * @return string
     */
    public function lastTransaction(string $wallet_address)
    {
        return $this->get(sprintf('wallet/%s/last_tx', $wallet_address));
    }

    /**
     * Get the reward value for a message of size $byte_size.
     *
     * @param  int $byte_size Message size in bytes
     *
     * @return int
     */
    public function getReward($byte_size): int
    {
        return $this->get(sprintf('price/%s', $byte_size));
    }

    /**
     * Post a HTTP message to the network.
     *
     * @param  string $path API endpoint
     * @param  mixed[] $data Data to post
     *
     * @return
     */
    private function post(string $path, array $data = [])
    {
        $url = sprintf('http://%s:%s/%s', $this->ip, $this->port, $path);

        $handle = $this->handle;

        curl_reset($handle);
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($handle, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($handle);

        if (curl_getinfo($handle, CURLINFO_HTTP_CODE) != 200) {
            throw new Exception(sprintf("Arweave API - Unexpected response (%s)
            	\n%s\n",
                curl_getinfo($handle, CURLINFO_HTTP_CODE),
                $response
            ));
        }

        return $response;
    }

    /**
     * Get data from the network.
     *
     * JSON responses will be decoded and returned as a json_decoded array,
     * all other types will be returned as-is.
     *
     * @param  string $path API endpoint
     *
     * @return mixed
     */
    private function get(string $path)
    {
        $url = sprintf('http://%s:%s/%s', $this->ip, $this->port, $path);

        $handle = $this->handle;

        curl_reset($handle);

        curl_setopt($handle, CURLOPT_URL, $url);

        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 3);

        curl_setopt($handle, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($handle);

        if (curl_getinfo($handle, CURLINFO_HTTP_CODE) == 404) {
            throw new Exception('Arweave API - Resource not found');
        }

        if (curl_getinfo($handle, CURLINFO_HTTP_CODE) != 200) {
            throw new Exception(sprintf("Arweave API - Unexpected response (%s)
            	\n%s\n",
                curl_getinfo($handle, CURLINFO_HTTP_CODE),
                $response
            ));
        }

        if ($json = json_decode($response, true)) {
            return $json;
        }

        return $response;
    }

}
