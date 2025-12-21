<?php

namespace App\GraphQL\Queries;

use App\Models\Cat;

class CatQuery
{
    public function all($root, array $args)
    {
        return Cat::all();
    }

    public function find($root, array $args)
    {
        return Cat::find($args['id']);
    }

    public function findByNameExact($root, array $args)
    {
        $name = strtolower($args['name']);

        return Cat::whereRaw('LOWER(name) = ?', [$name])->first();
    }





    public function findCatsByName($root, array $args)
    {
        $name = strtolower($args['name']);
        $mode = strtolower($args['mode'] ?? 'contains');

        $query = Cat::query();

        switch ($mode) {
            case 'exact':
                $query->whereRaw('LOWER(name) = ?', [$name]);
                break;

            case 'starts':
                $query->whereRaw('LOWER(name) LIKE ?', [$name.'%']);
                break;

            case 'ends':
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.$name]);
                break;

            case 'like':
            case 'close':
            case 'contains':
            default:
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.$name.'%']);
        }

        return $query->get();
    }


    public function findCatsByNameOrDetail($root, array $args)
    {
        $search   = $args['search'];
        $mode     = strtolower($args['mode'] ?? 'contains'); // default to contains
        $operator = strtolower($args['operator'] ?? 'or');   // default to or

        // Normalize search term for case-insensitive matching
        $searchLower = strtolower($search);

        switch ($mode) {
            case 'exact':
                $op = '=';
                $nameExpr = $searchLower;
                $desExpr  = $searchLower;
                break;

            case 'starts':
                $op = 'like';
                $nameExpr = "{$searchLower}%";
                $desExpr  = "{$searchLower}%";
                break;

            case 'ends':
                $op = 'like';
                $nameExpr = "%{$searchLower}";
                $desExpr  = "%{$searchLower}";
                break;

            case 'like':   // alias for contains
            case 'close':  // alias for contains
            case 'contains':
            default:
                $op = 'like';
                $nameExpr = "%{$searchLower}%";
                $desExpr  = "%{$searchLower}%";
        }

        $query = Cat::query();

        if ($operator === 'and') {
            if ($op === '=') {
                $query->whereRaw('LOWER(name) = ?', [$nameExpr])
                    ->whereRaw('LOWER(des) = ?', [$desExpr]);
            } else {
                $query->whereRaw('LOWER(name) '.$op.' ?', [$nameExpr])
                    ->whereRaw('LOWER(des) '.$op.' ?', [$desExpr]);
            }
        } else {
            $query->where(function ($q) use ($op, $nameExpr, $desExpr) {
                if ($op === '=') {
                    $q->whereRaw('LOWER(name) = ?', [$nameExpr])
                    ->orWhereRaw('LOWER(des) = ?', [$desExpr]);
                } else {
                    $q->whereRaw('LOWER(name) '.$op.' ?', [$nameExpr])
                    ->orWhereRaw('LOWER(des) '.$op.' ?', [$desExpr]);
                }
            });
        }

        return $query->get();
    }


    public function last($_, array $args)
    {
        // Order by ID descending and take the first record
        return Cat::orderBy('id', 'desc')->first();
    }


}
