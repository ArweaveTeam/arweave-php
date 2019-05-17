<?php

use \Arweave\SDK\Arweave;
use \Arweave\SDK\Support\Wallet;

include __DIR__ . '/vendor/autoload.php';

$arweave = new Arweave('https', 'arweave.net', '443');

$jwk = json_decode(file_get_contents('key.json'), true);

$wallet =  new Wallet($jwk);

$tx = $arweave->createTransaction($wallet, [
    'target' => 'nQoflnhlpZwYuSHVQGYGTo41WR8MxBFfF9DNNbApoIp',
    'data' => 'Some data to send, along with 10 winston',
    'quantity' => '10',
    'tags' => [
        'test-key' => 'test-value'
    ]
]);

// Dump the encoded transaction
var_dump($tx);

// Verify the signature
var_dump($tx->verify());

// Commit the transaction to the network - once sent this can't be undone.
$arweave->api()->commit($tx);

// Wait a few seconds for the tx to propagate
sleep(10);

$status = $arweave->api()->getTransaction($tx->getAttribute('id'));

// Now print the status
var_dump($status);


// Get transaction ids
$transactionIds = $arweave->api()->arql([
    'op' => 'equals',
    'expr1' => 'App-Name',
    'expr2' => 'arweaveapps'
]);

// Dump the transaction ids
var_dump($transactionIds);
