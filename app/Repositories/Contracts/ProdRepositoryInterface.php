<?php

namespace App\Repositories\Contracts;

interface ProdRepositoryInterface
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
        bool $numTags = false
);

    public function findByCatId(?int $catid, string $orderBy = 'id', string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
  );

    public function findByName(string $name, ?int $catid = null, string $orderBy = 'id', string $direction = 'desc' ,
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    );

    public function create(array $data); // requires catid
    public function update(int $id, array $data); // requires catid
    public function delete(int $id);
    public function deleteMany(array $ids);
    public function deleteByCatId(int $catId);



}
