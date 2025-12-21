<?php

namespace App\Repositories\Contracts;

interface CatRepositoryInterface
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
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function deleteMany(array $ids);
}
