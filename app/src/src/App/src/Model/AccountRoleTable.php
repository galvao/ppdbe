<?php

declare(strict_types = 1);

namespace App\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

class AccountRoleTable
{
    private $gateway;

    public function __construct(TableGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function get(?array $clause = null)
    {
        $resultSet = $this->gateway->select($clause);

        if (!$resultSet) {
            return [];
        }

        return $resultSet;
    }
}
