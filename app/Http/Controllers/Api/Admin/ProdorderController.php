<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProdorderRequest;
use App\Services\ProdorderService;
use Illuminate\Http\Request;

class ProdorderController extends Controller
{
    protected ProdorderService $orders;

    public function __construct(ProdorderService $orders)
    {
        $this->orders = $orders;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $fields = ['id', 'prodid', 'quan', 'customer', 'created_at'];

        $orders = $this->orders->paginate($perPage, $fields, 'id', 'desc');

        // Transform to include product name
        $orders->getCollection()->transform(function ($order) {
            return [
                'id'         => $order->id,
                'prod_name'  => $order->prod ? $order->prod->name : null,
                'quan'       => $order->quan,
                'customer'   => $order->customer,
                'created_at' => $order->created_at,
            ];
        });

        return response()->json([
            'items' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    

    public function store(ProdorderRequest $request)
    {
        $data = $request->validated();
        $order = $this->orders->create($data, $request);

        return response()->json($order, 201);
    }

    public function update(int $id, ProdorderRequest $request)
    {
        $data = $request->validated();
        $order = $this->orders->update($id, $data, $request);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    public function destroy(int $id)
    {
        $deleted = $this->orders->delete($id);

        if (!$deleted) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function destroyMany(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->orders->deleteMany($ids);

        return response()->json(['message' => 'Selected orders deleted successfully.']);
    }
}
