<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileUploader extends Controller
{

    public function store(Request $request)
    {
        $is_last = $request->has('is_last') && $request->boolean('is_last');

        $file = $request->file('file');

        $path = Storage::disk('local')->path("chunks/{$file->getClientOriginalName()}");

        File::append($path, $file->get());

        if ($is_last) {
            $name = basename($path, '.part');
            File::move($path, public_path("uploaded_files/{$name}"));
        }

        $data_out = [
            'uploaded' => true,
            'is_last' => $is_last
        ];

        if($is_last && !empty($name)){
            $data_out['file'] = [
                'name' => $name,
                'path' => '/uploaded_files/' . $name
            ];
        }

        return response()->json($data_out);
    }


}
