<?php

namespace Wang\Pkg\Lib;

use DateTimeInterface;

trait SerializeDate
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
