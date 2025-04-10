<?php

declare(strict_types = 1);

namespace App\Model;

use App\Abstraction\Model;

class AccountRole extends Model
{
    protected ?int $id = null;
    protected ?string $label = null;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }
}
