## Arweave PHP SDK

This package allows us to interact with the Arweave network, we can use it to read and write transactions and data to the network.

## Installation
We strongly recommend using [composer](https://getcomposer.org) for installation.

`composer require arweave/arweave-sdk`

Or add the following to your project `composer.json` file.

```
"require": {
   "arweave/arweave-sdk": "0.2.0"
}
```

## Quick Examples


#### Sending data to the network 


```php
include __DIR__ . '/vendor/autoload.php';

$arweave = new \Arweave\SDK\Arweave('http', '209.97.142.169', 1984);

$jwk = json_decode(file_get_contents('jwk.json'), true);

$wallet =  new \Arweave\SDK\Support\Wallet($jwk);

$transaction = $arweave->createTransaction($wallet, [
    'data' => '<html><head><title>Some page</title></head></html>',
    'tags' => [
        'Content-Type' => 'text/html'
    ]
]);

printf('Your transaction ID is %s', $transaction->getAttribute('id'));


// commit() sends the transaction to the network, once sent this can't be undone.
$arweave->commit($transaction);
```

#### Getting data from the network
```php
$arweave = new \Arweave\SDK\Arweave('http', '209.97.142.169', 1984);

$arweave->api()->getTransactionData('mvscO3JBlwweOnfkkHpc3fINQ6cUtn_g5aFY9af5TfQ')
```


## Usage


#### Instantiation
Start by creating a `Arweave` object, this is the primary SDK class your application should use, it contains the public methods for creating, sending and getting transactions.


```php
$arweave = new \Arweave\SDK\Arweave('http', '209.97.142.169', 1984);
```

Provide any valid Arweave node hostname or IP address

#### Getting a Transaction
Once we have our `Arweave` object we can now get transactions from the network using a valid transaction ID.

For example:
```php
$arweave->api()->getTransaction('mvscO3JBlwweOnfkkHpc3fINQ6cUtn_g5aFY9af5TfQ');
```

The above will return the following `Transaction` object:

```php
object(Arweave\SDK\Support\Transaction)#23 (1) {
  ["attributes":protected]=>
  array(10) {
    ["id"]=> string(43) "mvscO3JBlwweOnf..."
    ["last_tx"]=> string(43) "3MFrfH0-HI9GeMf..."
    ["owner"]=> string(683) "1Q7Rfgt23rfUDp..."
    ["target"]=> string(0) ""
    ["quantity"]=> string(1) "0"
    ["data"]=> string(60) "eyJib2R5IjoiVGVz..."
    ["reward"]=> string(10) "1825892857"
    ["signature"]=> string(683) "BUmdaf4rzlyT_3..."
    ["tags"]=> array(0) {}
  }
}
```

#### Getting data from a Transaction
There are two methods for getting data from a transaction, we can either:

```php
$data = $arweave->api()->getTransactionData($transaction_id);
//string(45) "{"body":"Test body","subject":"Test subject"}"
```

This method returns the original and decoded data from a transaction. This is the simplest method and probably the one you'll need most often.


Alternatively, if we need the encoded data or need other transaction attributes we can do the following:


```php
$transaction = $arweave->api()->getTransaction($transaction_id);

$encoded_data = $transaction->getAttribute('data');
//string(60) "eyJib2R5IjoiVGVzdCBib2R5Iiwic3ViamVjdCI6IlRlc3Qgc3ViamVjdCJ9"

$original_data = base64_decode(\Arweave\SDK\Support\Helpers::base64urlDecode($encoded_data));
//string(45) "{"body":"Test body","subject":"Test subject"}"
```
#### ArQL

```php
$transactionIds = $arweave->api()->arql([
    'op' => 'equals',
    'expr1' => 'App-Name',
    'expr2' => 'arweaveapps'
]);

// array(31) {
//   [0]=>
//   string(43) "NXg2OaRRygb7RJZFbkcEYlS2LNNfsqxxobzUqz7ELnc"
//   [1]=>
//   string(43) "i3_aC8xIO_4TpMqp5sR4WVUwbA1p2sPCu11cLVKN89U
// ...

```

#### Loading a Wallet
To load a wallet you need a Key file. Arweave uses JSON Web Keys (JWK) as the key file format, a JWK is simply a JSON representation of a public/private key pair and they look something like this:

```json
{
  "kty": "RSA",
  "ext": true,
  "e": "AQAB",
  "n": "1Q7Rfgt23rfU...",
  "d": "Yk_Z0tGLpar_...",
  "p": "_lrlR3LXDjR4...",
  "q": "1m-NU2BaG2vU...",
  "dp": "qfU3LFSrN52...",
  "dq": "gk_Sb5cFAQQ...",
  "qi": "k65nfXdh4qx..."
}
``` 

We first need to decode our JWK file to a PHP array, then we can simply pass that array into a new `Wallet` object.

**You should treat your JWK as you would treat an API key or a password**. You should **never** expose them or place them in any publicly accessible location and **never** commit them to any version control system, **doing so will compromise your wallet and its contents**.

```php
$jwk = json_decode(file_get_contents('jwk.json'), true);

$wallet =  new \Arweave\SDK\Support\Wallet($jwk);`
```

This is just one suggested method of storing your JWK but there's no requirement that you store in as a JSON file, you could also store it in an environment variable, a database, a PHP file, or anywhere else. As long as it ends up as a PHP array it should work just fine.

#### Creating a Transaction
Transactions need to be signed for them to be accepted by the network, so **this step requires a wallet**.
