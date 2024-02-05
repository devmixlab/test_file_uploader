<?php

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    public static function forPartNotFound (string $name) : self {

        return new self("File with name `{$name}` to append to not found.");

    }

    public static function forPartThatAlreadyUploaded (string $name) : self {

        return new self("File with name `{$name}` to append to already fully uploaded.");

    }

    public static function forUniqueNameCantBeGenerated (string $name) : self {

        return new self("Unique name for file `{$name}` cant be generated.");

    }
}
