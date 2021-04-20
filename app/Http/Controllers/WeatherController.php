<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;


class WeatherController extends Controller
{
    public function getWeatherDatesByCityId($value, $from, $to)
    {
        $from_ts = strtotime($from);
        $to_ts = strtotime($to);
        $weather_actual_data = DB::table('weather_actual')
            ->select(
                'weather_info->dt_txt AS dt_txt',
                'weather_info',
                'cities.id',
                'cities.name'
            )
            ->leftJoin('cities', 'cities.id', '=', 'weather_actual.city_id')
            ->whereBetween('date_time', [$from_ts, $to_ts])
            ->whereIn('weather_actual.city_id', explode(',', $value))
            ->get();

        $weather_history_data = DB::table('weather_history')
            ->select(
                'weather_info->dt_txt AS dt_txt',
                'weather_info',
                'cities.id',
                'cities.name'
            )
            ->leftJoin('cities', 'cities.id', '=', 'weather_history.city_id')
            ->whereBetween('date_time', [$from_ts, $to_ts])
            ->whereIn('weather_history.city_id', explode(',', $value))
            ->get();

        $result = [];

        foreach ($weather_actual_data as $row) {
            $result[$row->id . '_' . $row->name][$row->dt_txt] = json_decode($row->weather_info, true);
        }
        foreach ($weather_history_data as $row) {
            $result[$row->id . '_' . $row->name][$row->dt_txt] = json_decode($row->weather_info, true);
        }

        return $result;
    }

    public function getWeatherByCityId($id)
    {
        $weather_data = DB::table('weather_actual')
            ->select(
                'weather_info',
                'city_id',
                'weather_info->dt_txt AS dt_txt',
                'date_time',
                'cities.name')
            ->leftJoin('cities', 'cities.id', '=', 'weather_actual.city_id')
            ->whereIn('city_id', explode(',', $id))
            ->get();

        $result = [];
        foreach ($weather_data as $row) {
            $result[$row->city_id . '_' . $row->name][$row->dt_txt] =  json_decode($row->weather_info, true);
        }
        return $result;
    }

    public function createCity(Request $cities)
    {
        $client = new Client([
            'base_uri' => 'http://api.openweathermap.org/data/2.5/'
        ]);

        $cities_data = [];
        $cities_array = explode(',', $cities->name);

        DB::beginTransaction();

        foreach ($cities_array as $city_name) {
            if ((int)$city_name !== 0) {
                return json_encode(['error' => 'Enter string city name!']);
            }
            $response = $client->request('GET', 'forecast', [
                'query' => [
                    'q' => $city_name,
                    'appid' => '3499ef87135d738a61df0db636956f90'
                ],
            ]);

            $result = $response->getBody()->getContents();
            $weather_array = json_decode($result, true);
            $city_data = [
                'id' => $weather_array['city']['id'],
                'name' => $weather_array['city']['name']
            ];
            $cities_data[] = $city_data;

            DB::table('cities')
                ->insert($city_data);

            foreach ($weather_array['list'] as $weather_row) {
                DB::table('weather_actual')
                    ->insert([
                        'id' => $weather_row['dt'] . '_' . $weather_array['city']['id'],
                        'weather_info' => json_encode($weather_row),
                        'city_id' => $weather_array['city']['id'],
                        'date_time' => $weather_row['dt'] - $weather_array['city']['timezone']
                    ]);
            }
        }

        DB::commit();
        return json_encode(['inserted_cities' => $cities_data]);
    }

    public function deleteCity(Request $cities)
    {
        $deleted_cities_ids = explode(',', $cities->id);
        DB::table('cities')
            ->whereIn('id', $deleted_cities_ids)
            ->delete();

        return json_encode(['deleted_cities' => $deleted_cities_ids]);
    }
}
