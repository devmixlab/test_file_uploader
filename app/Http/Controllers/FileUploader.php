<?php

namespace App\Http\Controllers;

use App\Exceptions\FileUploadException;
use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\File as FileModel;
use App\Enums\FileUploadSequence;


class FileUploader extends Controller
{

    public function store(FileUploadRequest $request)
    {
        $file = $request->file('file');

        $sequence = FileUploadSequence::tryFrom($request->sequence);

        if($sequence == FileUploadSequence::FIRST){
            $client_original_name = $file->getClientOriginalName();
            $client_original_name = rtrim($client_original_name, '.part');
            $file_model = FileModel::create([
                "client_name" => $client_original_name,
                "name" => uniqid(). '.' . File::extension($client_original_name),
            ]);

            $file_model->save();
        }else{
            $file_model = FileModel::where([
                "name" => $request->name,
            ])->first();

            if(empty($file_model))
                return throw FileUploadException::forPartNotFound($request->name);
            if($file_model->status !== 'uploading')
                return throw FileUploadException::forPartThatAlreadyUploaded($request->name);
        }

        $path = Storage::disk('local')->path("chunks/{$file_model->part_name}");
        File::append($path, $file->get());

        if ($sequence == FileUploadSequence::LAST) {
            File::move($path, public_path("uploaded_files/{$file_model->name}"));
            $file_model->setAsUploaded()->save();
        }

        $data_out = [
            'uploaded' => true,
            'sequence' => $sequence->value,
            'name' => $file_model->name,
        ];

        if($sequence == FileUploadSequence::LAST){
            $data_out['file'] = [
                'path' => '/uploaded_files/' . $file_model->name,
                'client_name' => $file_model->client_name,
            ];
        }

        return response()->json($data_out);
    }


}
