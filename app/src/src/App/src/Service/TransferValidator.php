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

class TransferValidator
{
    public ?int $statusCode = null;
    public ?string $identifier = null;
    public ?string $reason = null;

    public function __construct($dbAdapter, $requestBody)
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);

        $requestBody->value = $moneyParser->parse((string)$requestBody->value, new Currency('BRL'));

        $sql = new Sql($dbAdapter);

        $query = $sql->select();
        $query->from('account');
        $query->join('account_role', 'account.role_id = account_role.id', ['label']);
        $query->join('wallet', 'account.id = wallet.account_id', ['balance']);
        $query->columns([
            'id',
        ]);
        $query->where(['account.id' => (int)$requestBody->payer]);

        $stmt = $dbAdapter->createStatement();
        $query->prepareStatement($dbAdapter, $stmt);
        $resultSet = $stmt->execute();

        $record = $resultSet->current();

        $this->statusCode = 200;
        $this->identifier = Uuid::uuid4()->toString();

        if ($record) {
            if ($record['label'] === 'Vendor') {
                $this->statusCode = 403;
                $this->reason = 'Lojistas não podem realizar transferências.';
            } else if ($moneyParser->parse((string)$record['balance'], new Currency('BRL'))->lessThan($requestBody->value)) {
                $this->statusCode = 403;
                $this->reason = 'Saldo insuficiente.';
            } else {
                print_r($moneyParser->parse((string)$record['balance'], new Currency('BRL'))->lessThan($requestBody->value));
                die();
            }
        } else {
            $this->statusCode = 404;
            $this->reason = 'Conta de origem não encontrada.';
        }
    }
}
