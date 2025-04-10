<?php

declare(strict_types = 1);

namespace App\Model;

use App\Abstraction\Model;

class TransferLog extends Model
{
    public ?int $id = null;
    public ?int $transfer_id = null;
    public ?string $status = null;
    public ?int $retry = null;
    public ?string $time_stamp = null;

    public function __construct(array|object $data = [])
    {
        $this->hydrate($data);
    }
}
