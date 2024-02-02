<?php

namespace App\Services\FileUploader;

use App\Exceptions\FileUploadException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\File as FileModel;
use App\Enums\FileUploadSequence;


class Uploader
{
    protected string $file_name = 'file';
    protected $file = null;
    protected ?FileModel $file_model = null;
    protected ?FileUploadSequence $sequence = null;
    protected $public_uploaded_files_directory = "uploaded_files";
    protected $storage_disk_auploaded_parts_directory = "chunks";

    public function __construct(protected Request $request) {
        $this->file = $request->file($this->file_name);
        $this->sequence = FileUploadSequence::tryFrom($request->sequence);
    }

    protected function isSequenceIn(array $in) : bool
    {
        return in_array($this->sequence, $in);
    }

    protected function getPath(array $path, $separator = DIRECTORY_SEPARATOR, $prepend_separator = false) : string
    {
        return ($prepend_separator ? $separator : '') . implode($separator, $path);
    }

    protected function setModel() : self
    {
        if($this->isSequenceIn([FileUploadSequence::FIRST, FileUploadSequence::FIRST_LAST])){
            $client_original_name = $this->file->getClientOriginalName();
            $client_original_name = rtrim($client_original_name, '.part');
            $file_model = FileModel::create([
                "client_name" => $client_original_name,
                "name" => uniqid(). '.' . File::extension($client_original_name),
            ]);

            $file_model->save();
        }else{
            $file_model = FileModel::where([
                "name" => $this->request->name,
            ])->first();

            if(empty($file_model))
                throw FileUploadException::forPartNotFound($this->request->name);
            if($file_model->status !== 'uploading')
                throw FileUploadException::forPartThatAlreadyUploaded($this->request->name);
        }

        $this->file_model = $file_model;

        return $this;
    }

    public function upload() : self
    {
        $this->setModel();

        $path = Storage::disk('local')->path($this->getPath([$this->storage_disk_auploaded_parts_directory, $this->file_model->part_name]));
        File::append($path, $this->file->get());

        if($this->isSequenceIn([FileUploadSequence::LAST, FileUploadSequence::FIRST_LAST])){
            File::move($path, public_path($this->getPath([$this->public_uploaded_files_directory, $this->file_model->name])));
            $this->file_model->setAsUploaded()->save();
        }

        return $this;
    }

    public function getDataOut() : array
    {
        $out = [
            'uploaded' => true,
            'sequence' => $this->sequence->value,
            'name' => $this->file_model->name,
        ];

        if($this->isSequenceIn([FileUploadSequence::LAST, FileUploadSequence::FIRST_LAST])){
            $out['file'] = [
                'path' => $this->getPath([$this->public_uploaded_files_directory, $this->file_model->name], '/', true),
                'client_name' => $this->file_model->client_name,
            ];
        }

        return $out;
    }

}
