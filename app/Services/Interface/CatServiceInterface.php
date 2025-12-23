<?php

namespace App\Services\Interface;

use Illuminate\Http\Request;

interface CatServiceInterface
{
    public function all(string $orderBy = 'id', string $direction = 'desc' ,  
    bool $withProds = false,
    bool $withProdCount = false);

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withProds = false,
        bool $withProdCount = false
    );

   public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        bool $withProds = false,
        bool $withProdCount = false
    );

    
    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*'],
         bool $withProds = false,
        bool $withProdCount = false
    );


    public function findById(int $id,bool $withProds = false,bool $withProdCount = false);

    public function findByName(string $name, string $orderBy = 'id', string $direction = 'desc',
        bool $withProds = false,
        bool $withProdCount = false);

    public function create(
        array $data,
        ?Request $request = null,
        string $folder = 'uploads/cats/file',
        string $baseFileName = 'cat'
    );

    public function update(
        int $id,
        array $data,
        ?Request $request = null,
        string $folder = 'uploads/cats/file',
        string $baseFileName = 'cat'
    );


    public function delete(int $id);
    public function deleteMany(array $ids);
}
