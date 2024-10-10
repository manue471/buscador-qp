<?php

namespace App\Services\Order;
use App\Services\QueroPassagemApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderService extends QueroPassagemApiService
{
    public function search($request)
    {
        
        $params = [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'travelDate' => $request->input('travelDate'),
            'include-connections' => $request->input('include-connections'),
            'affiliateCode' => config('queropassagem.affiliate_code'),
        ];
        $validationErrors = $this->validateParams($params);
        if (!empty($validationErrors)) {
            return response()->json(['message' => $validationErrors], 400);
        }
        try {
            $response = $this->client->request('POST', 'new/search', [
                'json' => $params,
            ]);
            $orders = json_decode($response->getBody()->getContents(), true);

            usort($orders, function ($a, $b) {
                return strtotime($a['departure']['time']) <=> strtotime($b['departure']['time']);
            });

            return $orders;
        } catch (\Exception $e) {
            Log::error('Erro ao acessar a API: ' . $e->getMessage());
            return null;
        }
    }

    public function seatSearch($request)
    {
        $params = [
            'travelId' => $request->input('travelId'),
            'orientation' => $request->input('orientation'),
            'type' => $request->input('type')
        ];

        if (empty($request->input('travelId'))) {
            return response()->json(['message' => 'Identificador da viagem é obrigatório'], 400);
        }
        try {
            $response = $this->client->request('POST', 'new/seats', [
                'json' => $params,
            ]);
            $orders = json_decode($response->getBody()->getContents(), true);

            return $orders;
        } catch (\Exception $e) {
            Log::error('Erro ao acessar a API: ' . $e->getMessage());
            return null;
        }
    }



    public function validateParams($params)
    {
        $errors = [];

        $requiredParams = [
            'from' => 'O local de partida é obrigatório.',
            'to' => 'O local de chegada é obrigatório.',
            'travelDate' => 'A data da viagem é obrigatória.',
        ];

        foreach ($requiredParams as $required => $message) {
            if (empty($params[$required])) {
                $errors[] = $message;
            }
        }
        if (!empty($params['travelDate'])) {
            $travelDate = Carbon::parse($params['travelDate']);
            if ($travelDate->isPast()) {
                $errors[] = 'A data da viagem inserida é inválida.';
            }
        }
        return $errors;
    }
}