<?php

namespace App\Http\Controllers;

use App\Services\QueroPassagemApiService;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;

class QueroPassagemController extends Controller
{
    protected $apiService;
    protected $orderService;


    public function __construct(QueroPassagemApiService $apiService, OrderService $orderService)
    {
        $this->apiService = $apiService;
        $this->orderService = $orderService;
    }

    public function getCompanies()
    {
        $companies = $this->apiService->getCompanies();
        if (!$companies) {
            return response()->json(['error' => 'Não foi possível buscar os dados'], 500);
        }

        return response()->json($companies);
    }

    public function getStops()
    {
        $companies = $this->apiService->getStops();
        if (!$companies) {
            return response()->json(['error' => 'Não foi possível buscar os dados'], 500);
        }

        return response()->json($companies);
    }

    public function searchOrder(Request $request) {
        $orders = $this->orderService->search($request);
        if (!$orders) {
            return response()->json(['error' => 'Não foi possível buscar os dados'], 500);
        }
        return response()->json($orders);
    }
}
