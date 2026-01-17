<?php

namespace App\Repositories;

use App\Models\Prodorder;
use App\Repositories\Contracts\ProdorderRepositoryInterface;

class ProdorderRepository implements ProdorderRepositoryInterface
{
    protected string $defaultOrderBy = 'id';
    protected string $defaultDirection = 'desc';

    public function all(string $orderBy = 'id', string $direction = 'desc')
    {
        return Prodorder::with('prod')
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection)
            ->get();
    }

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc'
    ) {
        return Prodorder::with('prod')
            ->select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection)
            ->paginate($perPage);
    }

    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null
    ) {
        $query = Prodorder::with('prod')
            ->select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function create(array $data)
    {
        if (empty($data['prodid'])) {
            throw new \InvalidArgumentException('prodid is required to create a prodorder.');
        }

        return Prodorder::create($data);
    }

    public function update(int $id, array $data)
    {
        if (empty($data['prodid'])) {
            throw new \InvalidArgumentException('prodid is required to update a prodorder.');
        }

        $order = Prodorder::findOrFail($id);
        $order->update($data);
        return $order;
    }

    public function delete(int $id)
    {
        return Prodorder::destroy($id);
    }

    public function deleteMany(array $ids)
    {
        return Prodorder::destroy($ids);
    }

    public function deleteByProdId(int $prodid)
    {
        return Prodorder::where('prodid', $prodid)->delete();
    }
}
