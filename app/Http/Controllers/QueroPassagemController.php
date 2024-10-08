<?php

namespace App\Http\Controllers;

use App\Services\Order\OrderService;
use App\Services\Stop\StopService;
use Illuminate\Http\Request;

class QueroPassagemController extends Controller
{
    protected $apiService;
    protected $orderService;

    protected $stopService;


    public function __construct(OrderService $orderService, StopService $stopService)
    {
        $this->orderService = $orderService;
        $this->stopService = $stopService;
    }

    public function getStops()
    {
        $companies = $this->stopService->getStops();
        if (!$companies) {
            return response()->json(['error' => 'Não foi possível buscar os dados'], 500);
        }

        return response()->json($companies);
    }

    public function getStopsByKeyword(Request $request)
    {
        $stops = $this->stopService->searchStops($request->keyword);
        if (!$stops) {
            return response()->json(['error' => 'Não foi possível buscar os dados'], 500);
        }

        return response()->json($stops);
    }

    public function searchOrder(Request $request) {
        $orders = $this->orderService->search($request);
        if (!$orders) {
            return response()->json(['error' => 'Não foi possível buscar os dados'], 500);
        }
        return response()->json($orders);
    }
}
