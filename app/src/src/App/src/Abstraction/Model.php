<?php

declare(strict_types = 1);

namespace App\Abstraction;

abstract class Model
{
    protected function hydrate(array|object $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function extract()
    {
        return get_object_vars($this);
    }
}
