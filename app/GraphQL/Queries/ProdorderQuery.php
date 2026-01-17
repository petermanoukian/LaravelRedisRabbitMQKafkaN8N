<?php

namespace App\GraphQL\Queries;

use App\Models\Prodorder;

class ProdorderQuery
{
    // Fetch all prodorders with their product + category
    public function all($root, array $args)
    {
        return Prodorder::with('prod.cat')->get();
    }

    // Fetch a single prodorder by ID
    public function find($root, array $args)
    {
        return Prodorder::with('prod.cat')->find($args['id']);
    }

    // Fetch orders by product ID
    public function findByProdid($root, array $args)
    {
        return Prodorder::with('prod.cat')->where('prodid', $args['prodid'])->get();
    }

    // Fetch orders by customer string
    public function findByCustomer($root, array $args)
    {
        return Prodorder::with('prod.cat')
            ->whereRaw('LOWER(customer) LIKE ?', ['%' . strtolower($args['customer']) . '%'])
            ->get();
    }

    // Fetch the last order
    public function last($_, array $args)
    {
        return Prodorder::with('prod.cat')->orderBy('id', 'desc')->first();
    }
}
