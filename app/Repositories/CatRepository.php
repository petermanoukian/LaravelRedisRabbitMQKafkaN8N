<?php

namespace App\Repositories;

use App\Models\Cat;
use App\Repositories\Contracts\CatRepositoryInterface;

class CatRepository implements CatRepositoryInterface
{
    protected string $defaultOrderBy = 'id';
    protected string $defaultDirection = 'desc';

    public function all(string $orderBy = 'id', string $direction = 'desc')
    {
        return Cat::orderBy(
            $orderBy ?? $this->defaultOrderBy,
            $direction ?? $this->defaultDirection
        )->get();
    }

    public function paginate(
        int $perPage = 15,
        array $fields = ['*'],
        string $orderBy = 'id',
        string $direction = 'desc'
    ) {
        return Cat::select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection)
            ->paginate($perPage);
    }

    public function select(
        array $fields,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null
    ) {
        $query = Cat::select($fields)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        return $perPage
            ? $query->paginate($perPage)
            : $query->get();
    }

    
    public function search(
        array $criteria,
        string $orderBy = 'id',
        string $direction = 'desc',
        ?int $perPage = null,
        array $fields = ['*']
    ) {
        $query = Cat::select($fields);

        foreach ($criteria as $field => $value) {
            if (is_array($value) && isset($value['like'])) {
                $query->where($field, 'LIKE', '%' . $value['like'] . '%');
            } else {
                $query->where($field, $value);
            }
        }

        $query->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection);

        return $perPage
            ? $query->paginate($perPage)
            : $query->get();
    }



    public function findById(int $id)
    {
        return Cat::find($id);
    }



    public function findByName(string $name, string $orderBy = 'id', string $direction = 'desc')
    {
        return Cat::where('name', $name)
            ->orderBy($orderBy ?? $this->defaultOrderBy, $direction ?? $this->defaultDirection)
            ->first();
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
