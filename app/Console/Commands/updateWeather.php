<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class updateWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_weather:every_thirty_minutes_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update weather data every 30 minutes from openweathermap';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        $old_data = DB::table('weather_actual')
            ->where('date_time', '<=', time())
            ->get();

        $ids_to_delete = [];
        foreach ($old_data as $row) {
            $ids_to_delete[] = $row->id;
            DB::table('weather_history')
                ->insert([
                    'id' => $row->id,
                    'city_id' => $row->city_id,
                    'weather_info' => $row->weather_info,
                    'date_time' => $row->date_time
                ]);
        }

        if(!empty($ids_to_delete)) {
            DB::table('weather_actual')
                ->whereIn('id', $ids_to_delete)
                ->delete();
        }

        $cities_ids = DB::table('weather_actual')
            ->select('city_id')
            ->get();

        $client = new Client([
            'base_uri' => 'http://api.openweathermap.org/data/2.5/'
        ]);

        foreach ($cities_ids as $city_id) {
            $response = $client->request("GET", "forecast", [
                "query" => [
                    "id" => $city_id->city_id,
                    "appid" => '3499ef87135d738a61df0db636956f90'
                ],
            ]);

            $weather_data = $response->getBody()->getContents();
            $weather_array = json_decode($weather_data, true);

            foreach ($weather_array['list'] as $weather_row) {
                DB::table('weather_actual')
                    ->where('id', $weather_row['dt'] . '_' . $weather_array['city']['id'])
                    ->update([
                        'weather_info' => json_encode($weather_row)
                    ]);
            }
        }

        DB::commit();
        echo json_encode([
            'action' => 'update weather data',
            'success' => true
        ]);
    }
}
