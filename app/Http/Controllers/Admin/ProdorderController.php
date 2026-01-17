<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProdorderController extends Controller
{
   
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $fields = [
            'id', 'prodid', 'quan', 'customer', 'created_at'
        ];

        $orders = $this->orders->paginate(
            $perPage,
            $fields,
            'id',
            'desc'
        );

        // In the Blade view you’ll now render $order->prod->name instead of prodid
        return view('admin.prodorders.index', compact('orders'));
    }

    public function indexJson(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $fields = [
            'id', 'prodid', 'quan', 'customer', 'created_at'
        ];

        $orders = $this->orders->paginate(
            $perPage,
            $fields,
            'id',
            'desc'
        );

        // Transform to include product name instead of prodid
        $orders->getCollection()->transform(function ($order) {
            return [
                'id'         => $order->id,
                'prod_name'  => $order->prod ? $order->prod->name : null,
                'quan'       => $order->quan,
                'customer'   => $order->customer,
                'created_at' => $order->created_at,
            ];
        });

        return response()->json($orders);
    }

    public function indexRedisJson(Request $request)
    {
        $orders = [];
        $cursor = null;

        do {
            [$cursor, $keys] = Redis::scan($cursor, [
                'match' => '*prodorder:*',
                'count' => 100,
            ]);

            foreach ($keys as $key) {
                $cleanKey = str_replace(config('database.redis.options.prefix'), '', $key);
                $value = Redis::get($cleanKey);

                if ($value !== null) {
                    $decoded = json_decode($value, true);
                    if ($decoded) {
                        // Replace prodid with prod_name if present in cached payload
                        if (isset($decoded['prod']) && isset($decoded['prod']['name'])) {
                            $decoded['prod_name'] = $decoded['prod']['name'];
                        }
                        unset($decoded['prodid']); // optional: drop raw prodid
                        $orders[] = $decoded;
                    }
                }
            }
        } while ($cursor != 0);

        return response()->json([
            'total' => count($orders),
            'data'  => $orders,
        ]);
    }





}
