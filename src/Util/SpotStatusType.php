<?php

namespace App\Util;

use App\Document\Status;
use Doctrine\ODM\MongoDB\Types\ClosureToPHP;
use Doctrine\ODM\MongoDB\Types\Type;

class SpotStatusType extends Type
{
    use ClosureToPHP;

    public function convertToPHPValue($value): Status
    {
        return Status::from($value);
    }

    public function convertToDatabaseValue($value): string
    {
        return $value->getValue();
    }
}
