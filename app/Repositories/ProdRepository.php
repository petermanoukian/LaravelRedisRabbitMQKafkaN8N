<?php

namespace App\Repositories;

use App\Models\Prod;
use App\Repositories\Contracts\ProdRepositoryInterface;

class ProdRepository implements ProdRepositoryInterface
{
    protected string $defaultOrderBy = 'id';
    protected string $defaultDirection = 'desc';

    
    public function all(
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        $query = Prod::orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($withCat) {
            $query->with('cat');
        }
        if ($withOrders) {
            //$query->with('orders');
        }
        if ($numOrders) {
           //$query->withCount('orders');
        }
        if ($withTags) {
            //$query->with('tags');
        }
        if ($numTags) {
            //$query->withCount('tags');
        }

        return $query->get();
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
        $query = Prod::select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($catid !== null) {
            $query->where('catid', $catid);
        }

        if ($withCat) {
            $query->with('cat');
        }
        if ($withOrders) {
            //$query->with('orders');
        }
        if ($numOrders) {
            //$query->withCount('orders');
        }
        if ($withTags) {
            //$query->with('tags');
        }
        if ($numTags) {
            //$query->withCount('tags');
        }

        return $query->paginate($perPage);
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
        $query = Prod::select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($withCat) {
            $query->with('cat');
        }
        if ($withOrders) {
            //$query->with('orders');
        }
        if ($numOrders) {
            //$query->withCount('orders');
        }
        if ($withTags) {
            //$query->with('tags');
        }
        if ($numTags) {
            //$query->withCount('tags');
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
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
        $query = Prod::select($fields);

        foreach ($criteria as $field => $value) {
            if (is_array($value) && isset($value['like'])) {
                $query->where($field, 'LIKE', '%' . $value['like'] . '%');
            } else {
                $query->where($field, $value);
            }
        }

        if ($withCat) {
            $query->with('cat');
        }
        if ($withOrders) {
            //$query->with('orders');
        }
        if ($numOrders) {
            //$query->withCount('orders');
        }
        if ($withTags) {
            //$query->with('tags');
        }
        if ($numTags) {
            //$query->withCount('tags');
        }

        $query->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function findById(
        int $id,
        bool $withCat = false,
        bool $withOrders = false,
        bool $numOrders = false,
        bool $withTags = false,
        bool $numTags = false
    ) {
        $query = Prod::where('id', $id);

        if ($withCat) {
            $query->with('cat');
        }
        if ($withOrders) {
            //$query->with('orders');
        }
        if ($numOrders) {
            //$query->withCount('orders');
        }
        if ($withTags) {
            //$query->with('tags');
        }
        if ($numTags) {
            //$query->withCount('tags');
        }

        return $query->first();
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
        $query = Prod::orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($catid !== null) {
            $query->where('catid', $catid);
        }

        if ($withCat) {
            $query->with('cat');
        }
        if ($withOrders) {
            //$query->with('orders');
        }
        if ($numOrders) {
            //$query->withCount('orders');
        }
        if ($withTags) {
            //$query->with('tags');
        }
        if ($numTags) {
            //$query->withCount('tags');
        }

        return $query->get();
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
        $query = Prod::where('name', $name);

        if ($catid !== null) {
            $query->where('catid', $catid);
        }

        if ($withCat) {
            $query->with('cat');
        }
        if ($withOrders) {
            //$query->with('orders');
        }
        if ($numOrders) {
            //$query->withCount('orders');
        }
        if ($withTags) {
            //$query->with('tags');
        }
        if ($numTags) {
            //$query->withCount('tags');
        }

        return $query->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection)
            ->first();
    }

    public function create(array $data)
    {
        if (empty($data['catid'])) {
            throw new \InvalidArgumentException('catid is required to create a product.');
        }

        return Prod::create($data);
    }

    public function update(int $id, array $data)
    {
        if (empty($data['catid'])) {
            throw new \InvalidArgumentException('catid is required to update a product.');
        }

        $prod = Prod::findOrFail($id);
        $prod->update($data);
        return $prod;
    }

    public function delete(int $id)
    {
        return Prod::destroy($id);
    }

    public function deleteMany(array $ids)
    {
        return Prod::destroy($ids);
    }

    public function deleteByCatId(int $catId) 
    { 
        return Prod::where('catid', $catId)->delete(); 
    }



}
