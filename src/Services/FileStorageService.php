<?php


namespace Kesify\MicroserviceSkeleton\Services;


use Illuminate\Http\UploadedFile;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Kesify\MicroserviceSkeleton\Models\FileStorage;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;

class FileStorageService
{

    /**
     *  Store a file and return its metadata and URL.
     * @param array|string|UploadedFile $file
     * @param $configuration
     * @param array $externFillables
     * @return array|null
     */
    public function store(array|string|UploadedFile $file, ?string $configurationName = 'default', array $externFillables = []): ?array
    {
        $file = $this->prepareUploadedFile($file);
        if (!$file) {
            return null;
        }

        $configuration = $this->getUploadConfig($configurationName);
        if (!$configuration || !$file) {
            return null;
        }

        if (!$this->validateFile($file, $configuration)) {
            return null;
        }

        $fillables = $this->getFillables($file, $externFillables);
        $path = $this->preparePath($configuration['path'], $fillables);

        $stored = $this->storeFile($file, $path, $configuration['disk']);
        if(!$stored)
            return null;


        $createData = array_merge($fillables,[
            'size'=>$file->getSize(),
            'extension'=>$file->getClientOriginalExtension(),
            'path'=>$path,
        ]);
        $metaData = $this->saveFileMetadata($createData, $configuration);
        $url = Storage::disk($configuration['disk'])->url($path);

        $returnData = array_merge(
            $metaData,
            ['url' => $url]
        );

        if (isset($configuration['afterUpload']) && is_callable($configuration['afterUpload'])) {
            call_user_func($configuration['afterUpload'], array_merge(['upload' => $returnData],['fileData' => $createData]));
        }

        return $returnData;
    }

    public function get($fileId, $configurationName = 'default'): ?array
    {
        $configuration = $this->getUploadConfig($configurationName);
        if (!$configuration || !$fileId) {
            return null;
        }

        $fileMetaData = $this->getFileMetadata($fileId, $configuration);

        $disk = Storage::disk($fileMetaData->disk);
        $fileContent = $disk->get($fileMetaData->path);
        $mimeType = $disk->mimeType($fileMetaData->path);

        return [
            'meta_data' => $fileMetaData,
            'file_content' => $fileContent,
        ];
    }

    public function getByFileName($fileName, $configurationName = 'default')
    {
        $configuration = $this->getUploadConfig($configurationName);
        if (!$configuration || !$fileName) {
            return null;
        }

        $connection = $configuration['connection'] ?? config('database.default');

        $fileMetaData = FileStorage::on($connection)->where('filename',$fileName)->where('configuration',$configurationName)->first();
        return $fileMetaData;
    }

    /**
     * @throws \Exception
     */
    public function move($fileId,$fromConfigurationName, $toConfigurationName = 'default', $externFillables = []): ?array
    {
        $fromConfiguration = $this->getUploadConfig($fromConfigurationName);
        $toConfiguration = $this->getUploadConfig($toConfigurationName);
        if (!$fromConfiguration || !$toConfiguration || !$fileId) {
            return null;
        }

        $fileMetaData = ($this->getFileMetadata($fileId,$fromConfiguration))->toArray();
        $file = $this->prepareUploadedFile([
            'name' => $fileMetaData['filename'],
            'path' => $fileMetaData['path'],
            'mime' => $fileMetaData['extension'],
        ]);
        $fillables = $this->getFillables($file, $externFillables);
        $path = $this->preparePath($toConfiguration['path'], $fillables);
        $moved = $this->moveFile($fileId, $fromConfiguration,$toConfiguration['disk'],$path);
        if(!$moved)
            return null;

        $updateData = [
            'path'=>$path,
        ];
        $metaData = $this->updateFileMetadata($fileId,$updateData,$toConfiguration);
        $url = Storage::disk($toConfiguration['disk'])->url($path);

        $returnData = array_merge(
            $metaData,
            ['url' => $url]
        );

        if (isset($toConfiguration['afterUpload']) && is_callable($toConfiguration['afterUpload'])) {
            call_user_func($toConfiguration['afterUpload'], array_merge(['upload' => $returnData],['fileData' => $metaData]));
        }

        return $returnData;
    }

    private function validateFile($file, $config): bool
    {
        $size = $file->getSize();
        if (isset($config['maxSize']) && $size > $config['maxSize']) {
            throw new FormSizeFileException("File size too big", Response::HTTP_BAD_REQUEST);
        }
        return true;
    }

    private function getFillables($file, $externFillables): array
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $user_id = Auth::id();
        $organization = App::get('organization');
        $generatedFileName = new KeyService()->generateName(16);

        return array_merge([
            'filename' => $filename,
            'extension' => $extension,
            'user_id' => $user_id,
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'generated_filename' => $generatedFileName
        ], $externFillables);
    }

    private function storeFile($file, $path, $disk): bool
    {
        try {
            return Storage::disk($disk)->put($path, file_get_contents($file));
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function deleteFile($path, $disk): void
    {
        try {
            Storage::disk($disk)->delete($path);
            return;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws \Exception
     */
    private function moveFile($fileId, $fromConfiguration, $toDisk, $toPath): ?bool
    {
        try {
            $fileMetaData = $this->getFileMetadata($fileId,$fromConfiguration);
            if($fileMetaData){
                $file = Storage::disk($fileMetaData->disk)->get($fileMetaData->path);
                return $file ? Storage::disk($toDisk)->put($toPath, $file):null;
            }

            return null;
        }catch (\Exception $e){
           return throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function saveFileMetadata($data, $configuration): array
    {
        $connection = $configuration['connection'] ?? config('database.default');
        $query = FileStorage::on($connection);

        if ($configuration['deleteRestOnSameConfiguration'] ?? false) {
            $query->where([
                'configuration' => $configuration['name'],
                'deleted' => 0
            ])->update(['deleted' => 1]);
        }


        $file = $query->create([
            'user_id'=>$data['user_id'],
            'filename'=>$data['filename'],
            'extension'=>$data['extension'],
            'generated_filename' => $data['generated_filename'],
            'size' => $data['size'],
            'disk' => $configuration['disk'],
            'path' => $data['path'],
            'configuration' => $configuration['name'],
            'active' => isset($configuration['markAsInactive']) && !$configuration['markAsInactive'],
        ]);

        return ['id' => $file->id, 'path' => $file->path];
    }

    public function updateFileMetadata($fileId, $data, $configuration): array
    {
        $connection = $configuration['connection'] ?? config('database.default');
        $query = FileStorage::on($connection);
        $file = $query->findOrFail($fileId);

        if ($configuration['deleteRestOnSameConfiguration'] ?? false) {
            $query->where([
                'configuration' => $configuration['name'],
                'deleted' => 0
            ])->update(['deleted' => 1]);
        }

        $data = array_merge($data,[
            'disk' => $configuration['disk'],
            'configuration' => $configuration['name'],
            'active' => !isset($configuration['markAsInactive']) || $configuration['markAsInactive'] === false,
        ]);

        $file->update($data);

        return ['id' => $file->id, 'path' => $file->path];
    }

    public function getFileMetadata($fileId,$configuration): FileStorage
    {
        $connection = $configuration['connection'] ?? config('database.default');

        return FileStorage::on($connection)->find($fileId);
    }


    /**
     * @param $type
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed|null
     */
    public function getUploadConfig($type = null): mixed
    {
        if($type){
            return config('filestorage.type.'.$type);
        }

        return $type;
    }

    /**
     * @param $path
     * @param array $fillable
     * @return array|mixed|string|string[]
     */
    public function preparePath($path, array $fillable): string
    {
        foreach ($fillable as $key => $value){
            $path = str_replace('{{'.$key.'}}',$value,$path);
        }
        return $path;
    }

    /**
     * @param string $fileId
     * @throws \Exception
     */
    public function softDelete(string $fileId): true
    {
        $fileRecord = FileStorage::find($fileId);
        if ($fileRecord) {
            $fileRecord->update(['deleted' => 1]);
        }

        return true;
    }

    /**
     * @param string $fileId
     * @throws \Exception
     */
    public function delete(string $fileId): true
    {
        $fileRecord = FileStorage::find($fileId);
        if ($fileRecord) {
            $this->deleteFile($fileRecord->path, $fileRecord->disk);
            $fileRecord->delete();
        }

        return true;
    }

    /**
     * Prepare the file as an UploadedFile instance.
     *
     * @param UploadedFile|string|array $file
     * @return UploadedFile|null
     */
    protected function prepareUploadedFile($file): ?UploadedFile
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }

        if (is_array($file) && isset($file['path'], $file['name'])) {
            return new UploadedFile(
                $file['path'],
                $file['name'],
                $file['mime'] ?? mime_content_type($file['path']),
                $file['error'] ?? 0
            );
        }

        if (is_string($file) && file_exists($file)) {
            return new UploadedFile(
                $file,
                basename($file),
                mime_content_type($file),
                0
            );
        }

        return null;
    }

}
