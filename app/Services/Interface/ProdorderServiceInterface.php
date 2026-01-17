<?php

namespace App\Services\Interface;

use App\Http\Requests\ProdorderRequest;

interface ProdorderServiceInterface
{
    // Basic retrieval
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

    // Core CRUD
    public function create(
        array $data,
        ?ProdorderRequest $request = null
    ); // requires prodid

    public function update(
        int $id,
        array $data,
        ?ProdorderRequest $request = null
    ); // requires prodid

    public function delete(int $id);

    public function deleteMany(array $ids);

    // Cleanup by product
    public function deleteByProdId(int $prodid);
}
