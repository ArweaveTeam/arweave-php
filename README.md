##Arweave PHP SDK

This package allows us to interact with the Arweave network, we can use it to read and write transactions and data to the network.

##Installation
We strongly recommend using [composer](https://getcomposer.org) for installation.

`composer require arweave/arweave-sdk`

Or add the following to your project `composer.json` file.

```
"require": {
   "arweave/arweave-sdk": "dev-master"
}
```

##Basic usage


####Instantiation
Start by creating a `Arweave` object, this is the primary SDK class your application should use, it contains the public methods for creating, sending and getting transactions.


```php
$arweave = new \Arweave\SDK\Arweave('139.59.81.47');
```

The IP address given should be any valid Arweave node IP.

####Getting a Transaction
Once we have our `Arweave` object we can now get transactions from the network using a valid transaction ID.

For example:
```php
$arweave->getTransaction('mvscO3JBlwweOnfkkHpc3fINQ6cUtn_g5aFY9af5TfQ');
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
    ["type"]=> string(4) "data"
    ["data"]=> string(60) "eyJib2R5IjoiVGVz..."
    ["reward"]=> string(10) "1825892857"
    ["signature"]=> string(683) "BUmdaf4rzlyT_3..."
    ["tags"]=> array(0) {}
  }
}
```

####Getting data from a Transaction
There are two methods of getting data from a transaction, we can either:

```php
$data = $arweave->getData($transaction_id)
//string(45) "{"body":"Test body","subject":"Test subject"}"
```

This method returns the decoded data from a transaction. This is the simplest method and probably the one you'll need most often.

Alternatively, if we also need other transaction attributes we can do the following:



```php
use Arweave\SDK\Support\Helpers;

$transaction = $arweave->getTransaction($transaction_id);

$encoded_data = $transaction->getAttribute('data');
//string(60) "eyJib2R5IjoiVGVzdCBib2R5Iiwic3ViamVjdCI6IlRlc3Qgc3ViamVjdCJ9"

$original_data = base64_decode(Helpers::base64urlDecode($encoded_data));
//string(45) "{"body":"Test body","subject":"Test subject"}"
```
This will give us the raw data (base64url encoded), it's useful if we also need other attributes about the transaction as we can extract other values (owner, signature, tags, etc) from the same `Transaction` variable.



####Loading a Wallet
To load a wallet you need a JSON Web Key (JWK), a JWK is simply a JSON representation of a public/private key pair, they look something like this:

```json
{
  "kty": "RSA",
  "ext": true,
  "e": "RFE",
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

```php
$jwk = json_decode(file_get_contents('jwk.json'), true);

$wallet =  new \Arweave\SDK\Support\Wallet($jwk);`
```

####Creating a Transaction
Transactions need to be signed for them to be accepted by the network, so **this step requires a wallet**.

```php
$data = 'Your data to put on the Arweave';

$transaction = $arweave->createTransaction($wallet, $data));

$arweave->commit($transaction);
```


##Examples


####Sending data to the network 


```php
include __DIR__ . '/vendor/autoload.php';

$arweave = new \Arweave\SDK\Arweave('139.59.81.47');

$jwk = json_decode(file_get_contents('jwk.json'), true);

$wallet =  new \Arweave\SDK\Support\Wallet($jwk);

$data = json_encode([
	'message' => 'Some message',
	'data' => [
		'Some data 1',
		'Some data 2',
		'Some data 3',
	]
]);

$transaction = $arweave->createTransaction($wallet, $data));

$transaction_id = $transaction->getAttribute('id');

$arweave->commit($transaction);
```

####Getting data from the network
```php
$arweave = new \Arweave\SDK\Arweave('139.59.81.47');

$arweave->getData('mvscO3JBlwweOnfkkHpc3fINQ6cUtn_g5aFY9af5TfQ')
```

