<?php

declare(strict_types=1);

namespace App\Handler;

use \DateTimeImmutable;

use Money\{
    Currency,
    Currencies\ISOCurrencies,
    Formatter\DecimalMoneyFormatter,
    Money,
    Parser\DecimalMoneyParser,
};

use Laminas\{
    Db\Adapter\Adapter,
    Db\Sql\Sql,
    Diactoros\Response\JsonResponse,
};

use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use App\Model\{
    Transfer,
    TransferLog,
    DataGateway,
};

use App\Service\{
    Connector,
    TransferValidator
};

final class TransferHandler implements RequestHandlerInterface
{
    protected ?Adapter $dbAdapter = null;

    public function __construct(string $containerName, RouterInterface $router, $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $currencies = new ISOCurrencies();
        $formatter = new DecimalMoneyFormatter($currencies);

        $body = $request->getBody();
        $contents = json_decode($body->getContents());

        $validator = new TransferValidator($this->dbAdapter, $contents);

        if ($validator->statusCode !== 200) {
            return new JsonResponse(['reason' => $validator->reason], $validator->statusCode);
        }

        $contents->identifier = $validator->identifier;
        $contents->value = (float)$formatter->format($contents->value);

        $data = new Transfer($contents);
        $table = new DataGateway($this->dbAdapter);
        $table->save($data, 'transfer');

        $data->id = (int)$this->dbAdapter->getDriver()->getLastGeneratedValue('transfer_id_seq');

        $now = new DateTimeImmutable();

        $log = new TransferLog([
            'transfer_id' => $data->id,
            'status' => 'C',
            'retry' => 3,
            'time_stamp' => $now->format('Y-m-d H:i:s'),
        ]);

        $table->save($log, 'transfer_log');

        $this->updateWallet(false, (int)$contents->payer, $contents->value);

        $now = new DateTimeImmutable();
        $log->time_stamp = $now->format('Y-m-d H:i:s');

        if (!Connector::authorize()) {
            $log->status = 'D';
            if (Connector::reason !== null) {
                $log->status = 'P';
                $this->updateWallet(true, (int)$contents->payer, $contents->value);
            }
        } else {
            $log->status = 'A';
            $this->updateWallet(true, (int)$contents->payee, $contents->value);
        }

        $table->save($log, 'transfer_log');

        if (Connector::notify()) {
            $log->status = 'N';
            $table->save($log, 'transfer_log');
        } else if (Connector::reason !== null) {
            $log->status = 'F';
        }

        return new JsonResponse(['message' => 'OK'], 200);
    }

    private function updateWallet(bool $credit, int $accountId, $value)
    {
        $currencies = new ISOCurrencies();
        $formatter = new DecimalMoneyFormatter($currencies);
        $parser = new DecimalMoneyParser($currencies);

        $sql = new Sql($this->dbAdapter);
        $query = $sql->select();
        $query->from('wallet');
        $query->columns([
            'balance',
        ]);
        $query->where(['account_id' => $accountId]);

        $stmt = $this->dbAdapter->createStatement();
        $query->prepareStatement($this->dbAdapter, $stmt);
        $resultSet = $stmt->execute();

        $record = $resultSet->current();

        $value = $parser->parse((string)$value, new Currency('BRL'));
        $balance = $parser->parse((string)$record['balance'], new Currency('BRL'));

        if ($credit === false) {
            $result = $balance->subtract($value);
        } else {
            $result = $balance->add($value);
        }

        $total = (float)$formatter->format($result);

        $conclusion = $sql->update('wallet');
        $conclusion->set(['balance' => $total]);
        $conclusion->where('account_id = ' . $accountId);

        $conclusionStmt = $sql->prepareStatementForSqlObject($conclusion);
        $conclusionStmt->execute();
    }
}
