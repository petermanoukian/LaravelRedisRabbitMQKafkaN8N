<?php

namespace App\Repositories\Contracts;

interface CatRepositoryInterface
{
   public function all(string $orderBy = 'id', string $direction = 'desc' ,  bool $withProds = false, bool $withProdCount = false);

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc',  bool $withProds = false, bool $withProdCount = false
    );


    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null ,  bool $withProds = false, bool $withProdCount = false
    );


    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*'] ,  bool $withProds = false, bool $withProdCount = false
    );


    public function findById(int $id ,  bool $withProds = false, bool $withProdCount = false) ;
    public function findByName(
        string $name, string $orderBy = 'id', string $direction = 'desc' ,  
        bool $withProds = false, bool $withProdCount = false
    );
    
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function deleteMany(array $ids);

}
