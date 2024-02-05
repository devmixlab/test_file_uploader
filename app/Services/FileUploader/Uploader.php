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
    protected $file;
    protected bool $is_last;
    protected string $file_original_name;
    protected string $file_ext;
    protected ?FileModel $file_model = null;
    protected ?FileUploadSequence $sequence = null;
    protected string $storage_uploaded_files_directory = "public";
    protected string $storage_disk_auploaded_parts_directory = "chunks";

    public function __construct(protected Request $request) {
        $this->file = $request->file($this->file_name);
        $this->file_name = $this->file->getClientOriginalName();
        $this->file_ext = File::extension($this->file_name);
        $this->is_last = $request->has('is_last') ? $request->boolean('is_last') : false;
    }

    protected function getPath(array $path, $separator = DIRECTORY_SEPARATOR, $prepend_separator = false) : string
    {
        return ($prepend_separator ? $separator : '') . implode($separator, $path);
    }

    protected function generateUniqueName() : string
    {
        for($i = 0; $i < 100; $i++){
            $name = uniqid(). '.' . $this->file_ext;
            $isExist = (bool) FileModel::where("name", $name)->count();
            if(!$isExist)
                break;

            $name = null;
        }

        if(is_null($name))
            throw FileUploadException::forUniqueNameCantBeGenerated($this->file_name);

        return $name;
    }

    protected function setModel() : self
    {
        $file_model = FileModel::where([
            "name" => $this->file_name,
        ])->first();

        $this->file_model = empty($file_model) || $file_model->isUploaded() ?
            FileModel::create([
                "client_name" => $this->file_name,
                "name" => $this->generateUniqueName(),
            ]) : $file_model;

        return $this;
    }

    public function upload() : self
    {
        $this->setModel();

        $path = Storage::disk('local')->path($this->getPath([$this->storage_disk_auploaded_parts_directory, $this->file_model->name]));
        File::append($path, $this->file->get());

        if($this->is_last){
            Storage::move(
                $this->getPath([$this->storage_disk_auploaded_parts_directory, $this->file_model->name]),
                $this->getPath([$this->storage_uploaded_files_directory, $this->file_model->name])
            );
            $this->file_model->setAsUploaded()->save();
        }

        return $this;
    }

    public function getDataOut() : array
    {
        $out = [
            'name' => $this->file_model->name,
        ];

        if($this->is_last){
            $out['file'] = [
                'path' => Storage::url($this->file_model->name),
                'client_name' => $this->file_model->client_name,
            ];
        }

        return $out;
    }

}
