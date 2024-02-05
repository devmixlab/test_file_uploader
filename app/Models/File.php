<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['client_name','name'];

    public function setAsUploaded() : self {
        $this->status = 'uploaded';
        return $this;
    }

    public function isUploaded() : bool {
        return $this->status === 'uploaded';
    }
}
