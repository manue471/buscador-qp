<?php

namespace App\Services\Stop;
use App\Services\QueroPassagemApiService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StopService extends QueroPassagemApiService
{

    public function getStops($keyword)
    {
        try {
            $stops = Cache::remember('stops', 60 * 60, function () {
                $response = $this->client->request('GET', 'stops');
                $stops = json_decode($response->getBody()->getContents(), true);
                $stops = $this->filterStops($stops);
                return $stops;
            });
            if (!$keyword) {
                return response()->json($stops, 200);
            }

            $filteredStops = array_filter($stops, function ($stop) use ($keyword) {
                $stopName = Str::ascii(Str::lower($stop['name']));
                $keywordNormalized = Str::ascii(Str::lower($keyword));
            
                return Str::contains($stopName, $keywordNormalized);
            });
    
            if (!$filteredStops) {
                return response()->json(['error' => 'Não há resultados com o nome '.$keyword.''], 200);
            }
            return response()->json($filteredStops, 200);
        } catch (\Exception $e) {
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
}