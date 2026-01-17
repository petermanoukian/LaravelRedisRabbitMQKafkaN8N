<?php

namespace App\Services;

use App\Repositories\Contracts\ProdorderRepositoryInterface;
use App\Services\Interface\ProdorderServiceInterface;
use App\Http\Requests\ProdorderRequest;

class ProdorderService implements ProdorderServiceInterface
{
    protected ProdorderRepositoryInterface $orders;

    public function __construct(ProdorderRepositoryInterface $orders)
    {
        $this->orders = $orders;
    }

    public function all(string $orderBy = 'id', string $direction = 'desc')
    {
        return $this->orders->all($orderBy, $direction);
    }

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc'
    ) {
        return $this->orders->paginate($perPage, $fields, $orderBy, $direction);
    }

    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null
    ) {
        return $this->orders->select($fields, $orderBy, $direction, $perPage);
    }

    public function create(
        array $data,
        ?ProdorderRequest $request = null
    ) {
        if (!isset($data['quan']) || $data['quan'] === null) {
            $data['quan'] = 1;
        }

        return $this->orders->create($data);
    }

    public function update(
        int $id,
        array $data,
        ?ProdorderRequest $request = null
    ) {
        if (!isset($data['quan']) || $data['quan'] === null) {
            $data['quan'] = 1;
        }

        return $this->orders->update($id, $data);
    }


    public function delete(int $id)
    {
        return $this->orders->delete($id);
    }

    public function deleteMany(array $ids)
    {
        return $this->orders->deleteMany($ids);
    }

    public function deleteByProdId(int $prodid)
    {
        return $this->orders->deleteByProdId($prodid);
    }
}
