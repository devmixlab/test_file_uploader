<?php

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    public static function forUniqueNameCantBeGenerated (string $name) : self {

        return new self("Unique name for file `{$name}` cant be generated.");

    }
}
