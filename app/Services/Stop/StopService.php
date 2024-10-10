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
                return $this->filterStops($stops);
            });
    
            if (!$keyword) {
                return response()->json($stops);
            }
    
            $filteredStops = array_filter($stops, function ($stop) use ($keyword) {
                $stopName = Str::ascii(Str::lower($stop['name']));
                $keywordNormalized = Str::ascii(Str::lower($keyword));
                
                return Str::contains($stopName, $keywordNormalized);
            });
    
            if (empty($filteredStops)) {
                return response()->json(['error' => 'Não há resultados com o nome '.$keyword]);
            }
    
            return response()->json(array_values($filteredStops));
        } catch (\Exception $e) {
            Log::error('Erro ao acessar a API: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao acessar a API'], 500);
        }
    }
    

    public function filterStops($stops)
    {
        $filteredStops = array_map(function ($stop) {
            $stop['is_allowed'] = str_ends_with($stop['url'], '-sp') || str_ends_with($stop['url'], '-pr');
            
            if (!empty($stop['substops'])) {
                foreach ($stop['substops'] as $index => $substop) {
                    $isAllowedLocation = str_ends_with($stop['url'], '-sp') || str_ends_with($stop['url'], '-pr');
                    $stop['substops'][$index]['is_allowed'] = $isAllowedLocation;
                }
            }
            
            return $stop;
        }, $stops);
    
        usort($filteredStops, function ($a, $b) {
            return $b['is_allowed'] <=> $a['is_allowed'];
        });

        return $filteredStops;
    }
}