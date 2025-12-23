<?php

namespace App\Repositories;

use App\Models\Cat;
use App\Models\Prod;
use App\Repositories\Contracts\CatRepositoryInterface;

class CatRepository implements CatRepositoryInterface
{
    protected string $defaultOrderBy = 'id';
    protected string $defaultDirection = 'desc';

    public function all(string $orderBy = 'id', string $direction = 'desc', bool $withProds = false, bool $withProdCount = false)
    {
        $query = Cat::orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($withProds) {
            $query->with('prods');
        }
        if ($withProdCount) {
            $query->withCount('prods');
        }

        return $query->get();
    }

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc',
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        $query = Cat::select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($withProds) {
            $query->with('prods');
        }
        if ($withProdCount) {
            $query->withCount('prods');
        }

        return $query->paginate($perPage);
    }

    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        $query = Cat::select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($withProds) {
            $query->with('prods');
        }
        if ($withProdCount) {
            $query->withCount('prods');
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*'],
        bool $withProds = false,
        bool $withProdCount = false
    ) {
        $query = Cat::select($fields);

        foreach ($criteria as $field => $value) {
            if (is_array($value) && isset($value['like'])) {
                $query->where($field, 'LIKE', '%' . $value['like'] . '%');
            } else {
                $query->where($field, $value);
            }
        }

        if ($withProds) {
            $query->with('prods');
        }
        if ($withProdCount) {
            $query->withCount('prods');
        }

        $query->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function findById(int $id, bool $withProds = false, bool $withProdCount = false)
    {
        $query = Cat::where('id', $id);

        if ($withProds) {
            $query->with('prods');
        }
        if ($withProdCount) {
            $query->withCount('prods');
        }

        return $query->first();
    }

    public function findByName(string $name, string $orderBy = 'id', string $direction = 'desc', bool $withProds = false, bool $withProdCount = false)
    {
        $query = Cat::where('name', $name)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        if ($withProds) {
            $query->with('prods');
        }
        if ($withProdCount) {
            $query->withCount('prods');
        }

        return $query->first();
    }

    public function create(array $data)
    {
        return Cat::create($data);
    }

    public function update(int $id, array $data)
    {
        $cat = Cat::findOrFail($id);
        $cat->update($data);
        return $cat;
    }

    public function delete(int $id)
    {
        return Cat::destroy($id);
    }

    public function deleteMany(array $ids)
    {
        return Cat::destroy($ids);
    }


}
