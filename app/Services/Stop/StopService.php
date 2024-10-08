<?php

namespace App\Services\Stop;
use App\Services\QueroPassagemApiService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class StopService extends QueroPassagemApiService
{

    public function getStops()
    {
        try {
            $stops = Cache::remember('stops', 60 * 60, function () {
                $response = $this->client->request('GET', 'stops');
                $stops = json_decode($response->getBody()->getContents(), true);
                $stops = $this->filterStops($stops);
                return $stops;
            });
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
            $stop['is_allowed'] = Str::contains($stop['url'], ['-sp', '-pr']);
            
            if (!empty($stop['substops'])) {
                foreach ($stop['substops'] as $index => $substop) {
                    $isAllowedLocation =  Str::contains($substop['url'], ['-sp', '-pr']);
                    $stop['substops'][$index]['is_allowed'] = $isAllowedLocation;
                }
            }
            
            return $stop;
        }, $stops);
    }
    public function searchStops($keyword)
    {
        $stops = Cache::get('stops');

        if (!$stops) {
            return response()->json(['error' => 'Não há dados disponíveis em cache.'], 404);
        }

        $filteredStops = array_filter($stops, function ($stop) use ($keyword) {
            return Str::contains(Str::lower($stop['name']), Str::lower($keyword));
        });

        if (!$filteredStops) {
            return response()->json(['error' => 'Não há estações com o nome '.$keyword.''], 200);
        }

        return response()->json(array_values($filteredStops));
    }
}