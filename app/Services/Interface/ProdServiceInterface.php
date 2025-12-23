<?php

namespace App\Services\Interface;

use Illuminate\Http\Request;
use App\Http\Requests\FileUploadRequest; 
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\ProdRequest;

interface ProdServiceInterface
{
    public function all(string $orderBy = 'id', string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    );

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
    );

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
    );

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
    );

    public function findById(int $id,
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false);

    public function findByCatId(?int $catid, string $orderBy = 'id', string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    );

    public function findByName(string $name, ?int $catid = null, string $orderBy = 'id', string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ); 

    public function getCategoriesForDropdown(?int $preselectedCatId = null);


    public function create(
        array $data,
        ?ProdRequest $request = null,
        ?FileUploadRequest $fileRequest = null,
        ?ImageUploadRequest $imageRequest = null,
        string $fileFolder = 'uploads/prods/file',
        string $imageFolder = 'uploads/prods/img',
        string $thumbFolder = 'uploads/prods/img/thumb',
        string $baseFileName = 'prod'
    );

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
    );

    public function delete(int $id);

    public function deleteMany(array $ids);
}
