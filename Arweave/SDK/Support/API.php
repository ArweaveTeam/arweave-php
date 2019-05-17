<?php

namespace Arweave\SDK\Support;

use Arweave\SDK\Exceptions\TransactionNotFoundException;
use Arweave\SDK\Support\Transaction;
use Exception;

class API
{
    /**
     * Protocol to connect to node with.
     * 'http' or 'http'
     * @var string
     */
    private $protocol;

    /**
     * Node IP or hostname to connect to.
     *
     * @var string
     */
    private $host;

    /**
     * Network port to use.
     * 
     * Default should be 1984, 443, or 80
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
     * @param string $protocol
     * @param string $host
     * @param string $port
     */
    public function __construct($protocol, $host, $port)
    {
        if (!$protocol) {
            throw new Exception('No Protocol specified');
        }
    
        if (!$host) {
            throw new Exception('No host specified');
        }
    
        if (!$port) {
            throw new Exception('No Port specified');
        }

        $this->protocol = $protocol;
        $this->host     = $host;
        $this->port     = $port;

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
     * Get a transaction by its ID.
     *
     * @param  string $transaction_id
     *
     * @throws TransactionNotFoundException
     * 
     * @return mixed|null
     */
    public function getTransaction(string $transaction_id)
    {
        return new Transaction($this->get(sprintf('tx/%s', $transaction_id)));
    }

    /**
     * Get a transaction's status by its ID.
     * 
     * String values for pending transactions, e.g. "Pending".
     * 
     * Assoc array with the block info if the transaction has been accepted, e.g.
     * 
     * array(3) {
     *   ["block_indep_hash"]=>
     *   string(64) "hJm2wxoAgneZdRNy2dDFLZnNGlImsSjkhEytC1g2H8GJBzoef5bqi5dlkCzT4HUl"
     *   ["block_height"]=>
     *   int(190582)
     *   ["number_of_confirmations"]=>
     *   int(8)
     * }
     * 
     * @param  string $transaction_id
     *
     * @throws TransactionNotFoundException
     * 
     * @return string[]|string
     */
    public function getTransactionStatus(string $transaction_id)
    {
        return $this->get(sprintf('tx/%s/status', $transaction_id));
    }

    /**
     * Get the decoded data from a transaction by transaction ID.
     *
     * @param  string $transaction_id
     *
     * @throws TransactionNotFoundException
     * 
     * @return mixed|null
     */
    public function getTransactionData(string $transaction_id)
    {
        return base64_Decode(Helpers::base64urlDecode($this->get(sprintf('tx/%s/data', $transaction_id))));
    }

    /**
     * Get the last transaction ID for a given wallet address.
     *
     * @param  string $wallet_address
     *
     * @return string|null
     */
    public function getLastTransaction(string $wallet_address): string
    {
        return $this->get(sprintf('wallet/%s/last_tx', $wallet_address));
    }

    /**
     * Get the last transaction ID for the given wallet address.
     *
     * @param  string $wallet_address
     *
     * @return string|null
     */
    public function getBalance(string $wallet_address): string
    {
        return $this->get(sprintf('wallet/%s/balance', $wallet_address));
    }

    /**
     * Get the reward/fee for a transaction with the given data length.
     *
     * @param int $byte_size Number of bytes
     * @param string $address Wallet address of the recipient (optional)
     *
     * @return int
     */
    public function getReward($byte_size, $address = ''): int
    {
        return $this->get(sprintf('price/%s/%s', $byte_size, $address));
    }

    /**
     * Get matching transaction IDs from a given ArQL query,
     * 
     * @param string[] $query ArQL query object
     * 
     * @return string[]
     */
    public function arql(array $query): array
    {
        $response = $this->post('arql', $query);

        if (is_array($response)) {
            return $response;
        }

        return [];
    }

    /**
     * Post a HTTP message to the network.
     *
     * @param  string $path API endpoint
     * @param  mixed[] $data Data to post
     *
     * @return
     */
    public function post(string $path, array $data = [])
    {
        $url = sprintf('%s://%s:%s/%s', $this->protocol, $this->host, $this->port, $path);

        $handle = $this->handle;

        curl_reset($handle);
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($handle, CURLOPT_TIMEOUT, 10);
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

        if ($json = json_decode($response, true)) {
            return $json;
        }

        return $response;
    }

    /**
     * Get data from the network.
     *
     * @param  string $path API endpoint
     *
     * @return mixed
     */
    public function get(string $path)
    {
        $url = sprintf('%s://%s:%s/%s', $this->protocol, $this->host, $this->port, $path);

        $handle = $this->handle;

        curl_reset($handle);

        curl_setopt($handle, CURLOPT_URL, $url);

        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($handle, CURLOPT_TIMEOUT, 10);

        curl_setopt($handle, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if (in_array($status, [404, 410] )) {
            throw new TransactionNotFoundException(sprintf("Arweave API - Unexpected response %s',
                \n%s\n%s\n",
                $url,
                $status,
                $response
            ));
        }

        if (!in_array($status, [200, 202] )) {
            throw new Exception(sprintf("Arweave API - Unexpected response (%s)
            	\n%s\n",
                $status,
                $response
            ));
        }

        if ($json = json_decode($response, true)) {
            return $json;
        }

        return $response;
    }


}
