<?php

namespace App\Services;

use App\Repositories\Contracts\CatRepositoryInterface;
use App\Services\Interface\CatServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CatService implements CatServiceInterface
{
    protected CatRepositoryInterface $cats;
    protected FileUploaderService $fileUploader;

    public function __construct(CatRepositoryInterface $cats, FileUploaderService $fileUploader)
    {
        $this->cats = $cats;
        $this->fileUploader = $fileUploader;
    }

    public function all(string $orderBy = 'id', string $direction = 'desc')
    {
        return $this->cats->all($orderBy, $direction);
    }


    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc'
    ) {
        return $this->cats->paginate($perPage, $fields, $orderBy, $direction);
    }

    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null
    ) {
        return $this->cats->select($fields, $orderBy, $direction, $perPage);
    }


    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*']
    ) {
        return $this->cats->search($criteria, $orderBy, $direction, $perPage, $fields);
    }

    public function findById(int $id)
    {
        return $this->cats->findById($id);
    }

    public function findByName(string $name, string $orderBy = 'id', string $direction = 'desc')
    {
        return $this->cats->findByName($name, $orderBy, $direction);
    }

    public function create(array $data, ?Request $request = null, string $folder = 'uploads/cats/file', string $baseFileName = 'cat')
    {
        if ($request && $request->hasFile('filer')) {

            //\Log::info('Uploaded file MIME type: ' . $request->file('filer')->getMimeType());
            $upload = $this->fileUploader->upload(
                $request,
                'filer',
                $folder,        // passed from controller
                $baseFileName,  // passed from controller
                uniqid()
            );

            if ($upload) {
                $data['filer'] = $upload['path'];
                $data['filename'] = $upload['filename'];
                $data['mime'] = $upload['mime'];
                $data['sizer'] = $upload['size'];
                $data['extension'] = $upload['extension'];
            }
        }

        return $this->cats->create($data);
    }


    public function update(int $id, array $data, ?Request $request = null, string $folder = 'uploads/cats/file', string $baseFileName = 'cat')
    {
        if ($request && $request->hasFile('filer')) {
            $upload = $this->fileUploader->upload(
                $request,
                'filer',
                $folder,
                $baseFileName,
                uniqid()
            );

            if ($upload) {
                $data['filer'] = $upload['path'];
                $data['filename'] = $upload['filename'];
                $data['mime'] = $upload['mime'];
                $data['sizer'] = $upload['size'];
                $data['extension'] = $upload['extension'];
            }
        }

        return $this->cats->update($id, $data);
    }


    public function delete(int $id)
    {
        return $this->cats->delete($id);
    }

    public function deleteMany(array $ids)
    {
        return $this->cats->deleteMany($ids);
    }
}
