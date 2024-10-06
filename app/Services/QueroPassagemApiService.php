<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueroPassagemApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('queropassagem.base_url'),
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(config('queropassagem.user') . ':' . config('queropassagem.password')),
            ],
        ]);
    }

    public function getCompanies()
    {
        try {
            $response = $this->client->request('GET', 'companies');

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Erro ao acessar a API: ' . $e->getMessage());
            return null;
        }
    }

    public function getStops()
    {
        try {
            $response = $this->client->request('GET', 'stops');

            $stops = json_decode($response->getBody()->getContents(), true);
            $stops = $this->filterStops($stops);
            return $stops;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Erro ao acessar a API: ' . $e->getMessage());
            return null;
        }
    }

    public function filterStops($stops)
    {
        return array_map(function ($stop) {
            if (Str::contains($stop['name'], ['SP', 'PR'])) {
                $stop['is_allowed'] = true;
            } else {
                $stop['is_allowed'] = false;
            }
            return $stop;
        }, $stops);
    }
}