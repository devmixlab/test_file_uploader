<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Services\FileUploader\Uploader;


class FileUploader extends Controller
{

    public function store(FileUploadRequest $request)
    {
        $data_out = (new Uploader($request))
            ->upload()
            ->getDataOut();

        return response()->json($data_out);
    }

}
