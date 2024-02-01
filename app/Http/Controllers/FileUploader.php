<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileUploader extends Controller
{

    public function store(Request $request)
    {
//        var_dump(22);
//        die();

        $file = $request->file('file');

        $path = Storage::disk('local')->path("chunks/{$file->getClientOriginalName()}");

        File::append($path, $file->get());

        if ($request->has('is_last') && $request->boolean('is_last')) {

            $name = basename($path, '.part');

            File::move($path, public_path("uploaded_files/{$name}"));
        }


        return response()->json([
            'uploaded' => true,
            'is_last' => $request->is_last
        ]);
    }


}
