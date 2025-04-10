<?php

declare(strict_types = 1);

namespace App\Model;

use Laminas\{
    Db\Adapter\Adapter,
    Db\Sql\Sql,
};

use Ramsey\Uuid\Uuid;

use App\Abstraction\Model;

class DataGateway
{
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function save(Model $model, string $entityName, string $pk = 'id')
    {
        $columns = [];
        $values = [];

        foreach ($model as $key => $value) {
            $columns[] = $key;
            $values[] = $value;
        }

        
        $sql = new Sql($this->adapter);

        if ($model->$pk === null) {
            array_shift($columns);
            array_shift($values);

            $query = $sql->insert($entityName);
            $query->columns($columns);
            $query->values($values);

            /*if ($entityName == 'transfer_log') {*/
            /*    print_r($columns);*/
            /*    print_r($values);*/
            /*    die();*/
            /*}*/
        } else {
            $query = $sql->update($entityName);
            $data = [];

            for ($c = 1; $c < count($columns); $c++) { 
                $data[$columns[$c]] = $values[$c];
            }

            $query->set($data);
            $query->where($pk . '=' . $model->$pk);

        }


        $stmt = $sql->prepareStatementForSqlObject($query);
        $stmt->execute();
    }
}
