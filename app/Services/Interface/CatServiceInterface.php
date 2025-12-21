<?php

namespace App\Services\Interface;

use Illuminate\Http\Request;

interface CatServiceInterface
{
    public function all(string $orderBy = 'id', string $direction = 'desc');
    public function paginate(
    int $perPage = 15,
    array $fields = ['*'],
    string $orderBy = 'id',
    string $direction = 'desc'
    );

   public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null
    );

    
    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*']
    );


    public function findById(int $id);
    public function findByName(string $name, string $orderBy = 'id', string $direction = 'desc');

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
