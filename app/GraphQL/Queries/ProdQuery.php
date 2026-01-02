<?php

namespace App\GraphQL\Queries;

use App\Models\Prod;
use App\Models\Cat;

class ProdQuery
{
    public function all($root, array $args)
    {
        return Prod::all();
    }

    public function find($root, array $args)
    {
        return Prod::find($args['id']);
    }


    public function findByNameExact($root, array $args)
    {
        $name = strtolower($args['name']);
        return Prod::whereRaw('LOWER(name) = ?', [$name])->first();
    }

    public function findProdsByNameOrDetail($root, array $args)
    {
        $search   = $args['search'];
        $mode     = strtolower($args['mode'] ?? 'contains');
        $operator = strtolower($args['operator'] ?? 'or');
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
            default: // contains
                $op = 'like';
                $nameExpr = "%{$searchLower}%";
                $desExpr  = "%{$searchLower}%";
        }

        $query = Prod::query();

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




    public function findProdsByName($root, array $args)
    {
        $name = strtolower($args['name']);
        $mode = strtolower($args['mode'] ?? 'contains');

        $query = Prod::query();

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

    public function findProdByCid($root, array $args)
    {
        return Prod::where('catid', $args['catid'])->get();
    }

    public function findProdByCatName($root, array $args)
    {
        $catname = strtolower($args['catname']);
        $cat = Cat::whereRaw('LOWER(name) = ?', [$catname])->first();

        if (!$cat) {
            return collect(); // empty collection if no category found
        }

        return Prod::where('catid', $cat->id)->get();
    }



    public function last($_, array $args)
    {
        return Prod::orderBy('id', 'desc')->first();
    }
}
