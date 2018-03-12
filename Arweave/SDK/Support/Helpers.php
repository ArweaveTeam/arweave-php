<?php

namespace Arweave\SDK\Support;

class Helpers
{
    public static function base64urlDecode($data)
    {
        return str_pad(strtr($data, '-_', '+/'), strlen($data) + 4 - (strlen($data) % 4), '=', STR_PAD_RIGHT);
    }

    public static function base64urlEncode($data)
    {
        return str_replace('=', null, strtr($data, '+/', '-_'));
    }
}
