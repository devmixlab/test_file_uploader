<?php

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    public static function forPartNotFound (string $name) : self {

        $exception = new self("File with name `{$name}` to append to not found.");
        return $exception;

    }

    public static function forPartThatAlreadyUploaded (string $name) : self {

        $exception = new self("File with name `{$name}` to append to already fully uploaded.");
        return $exception;

    }
}
