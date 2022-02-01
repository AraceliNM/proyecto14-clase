<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return view('orders.index');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $items = json_decode($order->content);

        return view('orders.show', compact('order', 'items'));
    }
}
