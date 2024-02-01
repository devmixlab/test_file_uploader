<?php
namespace App\Enums;

enum FileUploadSequence: string
{
    case FIRST = 'first';
    case MIDDLE = 'middle';
    case LAST = 'last';
    case FIRST_LAST = 'first_last';
}
