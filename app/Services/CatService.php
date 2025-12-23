<?php

namespace App\Services;

use App\Repositories\Contracts\CatRepositoryInterface;
use App\Repositories\Contracts\ProdRepositoryInterface; 
use App\Services\Interface\CatServiceInterface;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CatService implements CatServiceInterface
{
    protected CatRepositoryInterface $cats;
    protected FileUploaderService $fileUploader;
    protected ProdRepositoryInterface $prods;

    public function __construct(CatRepositoryInterface $cats, FileUploaderService $fileUploader, ProdRepositoryInterface $prods)
    {
        $this->cats = $cats;
        $this->fileUploader = $fileUploader;
        $this->prods = $prods;
    }

        public function all(
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        return $this->cats->all($orderBy, $direction, $withProds, $withProdCount);
    }

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        return $this->cats->paginate($perPage, $fields, $orderBy, $direction, $withProds, $withProdCount);
    }

    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        return $this->cats->select($fields, $orderBy, $direction, $perPage, $withProds, $withProdCount);
    }

    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*'],
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        return $this->cats->search($criteria, $orderBy, $direction, $perPage, $fields, $withProds, $withProdCount);
    }

    public function findById(int $id, bool $withProds = false, bool $withProdCount = false)
    {
        return $this->cats->findById($id, $withProds, $withProdCount);
    }

    public function findByName(
        string $name,
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        return $this->cats->findByName($name, $orderBy, $direction, $withProds, $withProdCount);
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
        $this->prods->deleteByCatId($id);
        return $this->cats->delete($id);
    }

    public function deleteMany(array $ids)
    {
        foreach ($ids as $catId) 
        { 
            $this->prods->deleteByCatId($catId); 
        }
        return $this->cats->deleteMany($ids);
    }
}
