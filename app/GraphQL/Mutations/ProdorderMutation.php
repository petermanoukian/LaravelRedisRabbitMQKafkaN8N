<?php

namespace App\GraphQL\Mutations;
use App\Services\ProdorderService;

class ProdorderMutation
{
    protected ProdorderService $orders;

    public function __construct(ProdorderService $orders)
    {
        $this->orders = $orders;
    }

    public function createProdorder($root, array $args)
    {
        $validated = [
            'prodid'   => $args['prodid'],
            'quan'     => $args['quan'] ?? 1,
            'customer' => $args['customer'] ?? null,
        ];

        $order = $this->orders->create($validated);
        return $order;
    }

    public function updateProdorder($root, array $args)
    {
        $id = (int) $args['id'];

        $validated = [
            'prodid'   => $args['prodid'] ?? null,
            'quan'     => $args['quan'] ?? null,
            'customer' => $args['customer'] ?? null,
        ];

        $order = $this->orders->update($id, $validated);
        return $order;
    }

    public function deleteProdorder($root, array $args): bool
    {
        $id = (int) $args['id'];

        $order = $this->orders->findById($id);
        if (!$order) {
            return false;
        }

        $this->orders->delete($id);
        return true;
    }

    public function deleteProdorders($root, array $args): bool
    {
        $ids = $args['ids'] ?? [];

        if (empty($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            $order = $this->orders->findById($id);
            if ($order) {
                $this->orders->delete($id);
            }
        }

        return true;
    }
}
