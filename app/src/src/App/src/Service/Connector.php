<?php
declare(strict_types = 1);

namespace App\Service;

use Money\{
    Currency,
    Currencies\ISOCurrencies,
    Money,
    Parser\DecimalMoneyParser,
};

use Laminas\{
    Db\Adapter\Adapter,
    Db\Sql\Sql,
    Diactoros\Response\JsonResponse,
};

use Ramsey\Uuid\Uuid;

use GuzzleHttp\Client;

class Connector
{
    public static ?string $reason = null;

    public static function authorize(): bool
    {
        $url= 'https://util.devi.tools/api/v2/';

        $client = new Client(['base_uri' => $url, 'http_errors' => false]);

        $response = $client->request('GET', 'authorize');

        if ($response->getStatusCode() > 499) {
            self::$reason = $response->getReasonPhrase();
            return false;
        } else if ($response->getStatusCode() != 200) {
            return false;
        }

        return true;
    }

    public static function notify(): bool
    {
        $url= 'https://util.devi.tools/api/v1/';

        $client = new Client(['base_uri' => $url, 'http_errors' => false]);

        $response = $client->request('POST', 'notify');

        if ($response->getStatusCode() > 499) {
            self::$reason = $response->getReasonPhrase();
            return false;
        } else if ($response->getStatusCode() != 200) {
            return false;
        }

        return true;
    }
}

