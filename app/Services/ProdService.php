<?php

namespace App\Services;

use App\Repositories\Contracts\ProdRepositoryInterface;
use App\Repositories\Contracts\CatRepositoryInterface;
use App\Services\Interface\ProdServiceInterface;
use App\Services\FileUploaderService; 
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\ProdRequest;
use Illuminate\Support\Facades\Log;

class ProdService implements ProdServiceInterface
{
    protected ProdRepositoryInterface $prods;
    protected CatRepositoryInterface $cats; 
    protected FileUploaderService $fileUploader;
    protected ImageUploadService $imageUploader;

    public function __construct
    ( ProdRepositoryInterface $prods, CatRepositoryInterface $cats, 
    FileUploaderService $fileUploader, ImageUploadService $imageUploader ) 
    { 
        $this->prods = $prods; $this->cats = $cats; 
        $this->fileUploader = $fileUploader; 
        $this->imageUploader = $imageUploader; 
    }

    public function all(
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        return $this->prods->all($orderBy, $direction, $withCat, $withOrders, $numOrders, $withTags, $numTags);
    }

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $catid = null,
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        return $this->prods->paginate($perPage, $fields, $orderBy, $direction, $catid, 
        $withCat, $withOrders, $numOrders, $withTags, $numTags);
    }

    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        return $this->prods->select($fields, $orderBy, $direction, $perPage, 
        $withCat, $withOrders, $numOrders, $withTags, $numTags);
    }

    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*'],
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        return $this->prods->search($criteria, $orderBy, $direction, $perPage, $fields, 
        $withCat, $withOrders, $numOrders, $withTags, $numTags);
    }

    public function findById(
        int $id,
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        return $this->prods->findById($id, $withCat, $withOrders, $numOrders, $withTags, $numTags);
    }

    public function findByCatId(
        ?int $catid,
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        return $this->prods->findByCatId($catid, $orderBy, $direction, 
        $withCat, $withOrders, $numOrders, 
        $withTags, $numTags);
    }

    public function findByName(
        string $name,
        ?int $catid = null,
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        return $this->prods->findByName($name, $catid, $orderBy, $direction, 
        $withCat, $withOrders, $numOrders, $withTags, $numTags);
    }

    public function getCategoriesForDropdown(?int $preselectedCatId = null)
    {
        // Always order categories by name ascending
        $categories = $this->cats->all('name', 'asc');

        // If preselectedCatId is provided, mark it
        return $categories->map(function ($cat) use ($preselectedCatId) {
            $cat->selected = ($preselectedCatId !== null && $cat->id === $preselectedCatId);
            return $cat;
        });
    }

    public function create(
        array $data,
        ?ProdRequest $request = null,
        ?FileUploadRequest $fileRequest = null,
        ?ImageUploadRequest $imageRequest = null,
        string $fileFolder = 'uploads/prods/file',
        string $imageFolder = 'uploads/prods/img',
        string $thumbFolder = 'uploads/prods/img/thumb',
        string $baseFileName = 'prod'
    ) {
        if ($request && $request->hasFile('filer')) {
            $upload = $this->fileUploader->upload(
                $fileRequest,
                'filer',
                $fileFolder,
                $baseFileName,
                uniqid()
            );

            if ($upload) {
                $data['filer']     = $upload['path'];
                $data['filename']  = $upload['filename'];
                $data['mime']      = $upload['mime'];
                $data['sizer']     = $upload['size'];
                $data['extension'] = $upload['extension'];
            }
        }

        if ($request && $request->hasFile('img')) {
            $uploadImg = $this->imageUploader->upload(
                $imageRequest,
                'img',
                $imageFolder,
                $thumbFolder,
                1500,
                1000,
                $baseFileName
            );

            if ($uploadImg) {
                $data['img']    = $uploadImg['large'];
                $data['img2']    = $uploadImg['small'];
                Log::info("ProdService: image upload success", $uploadImg);
            }
        }

        return $this->prods->create($data);
    }

    public function update(
        int $id,
        array $data,
        ?ProdRequest $request = null, 
        ?FileUploadRequest $fileRequest = null,
        ?ImageUploadRequest $imageRequest = null,
        string $fileFolder = 'uploads/prods/file',
        string $imageFolder = 'uploads/prods/img',
        string $thumbFolder = 'uploads/prods/img/thumb',
        string $baseFileName = 'prod'
    ) {
        if ($request && $request->hasFile('filer')) {
            $upload = $this->fileUploader->upload(
                $fileRequest,
                'filer',
                $fileFolder,
                $baseFileName,
                uniqid()
            );

            if ($upload) {
                $data['filer']     = $upload['path'];
                $data['filename']  = $upload['filename'];
                $data['mime']      = $upload['mime'];
                $data['sizer']     = $upload['size'];
                $data['extension'] = $upload['extension'];
            }
        }

        if ($request && $request->hasFile('img')) {
            $uploadImg = $this->imageUploader->upload(
                $imageRequest,
                'img',
                $imageFolder,
                $thumbFolder,
                1500,
                1000,
                $baseFileName
            );

            if ($uploadImg) {
                $data['img']    = $uploadImg['large'];
                $data['img2']    = $uploadImg['small'];
            }
        }

        return $this->prods->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->prods->delete($id);
    }

    public function deleteMany(array $ids)
    {
        return $this->prods->deleteMany($ids);
    }
}
