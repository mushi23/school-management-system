<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Holiday;

class HolidayController extends Controller
{
    public function getKenyanHolidays($year = 2025)
    {
        $apiKey = env('CALENDARIFIC_API_KEY');

        $response = Http::get("https://calendarific.com/api/v2/holidays", [
            'api_key' => $apiKey,
            'country' => 'KE',
            'year' => $year
        ]);

        if ($response->successful()) {
            return response()->json($response['response']['holidays']);
        }

        return response()->json(['error' => 'Failed to fetch holidays'], 500);
    }


    public function syncKenyanHolidays($year = null)
    {
        $year = $year ?? now()->year;
        $apiKey = env('CALENDARIFIC_API_KEY');

        $response = Http::get("https://calendarific.com/api/v2/holidays", [
            'api_key' => $apiKey,
            'country' => 'KE',
            'year' => $year
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch holidays'], 500);
        }

        $holidays = $response['response']['holidays'];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']['iso']],
                [
                    'name' => $holiday['name'],
                    'country_code' => 'KE',
                    'type' => $holiday['type'][0] ?? null,
                ]
            );
        }

        return response()->json(['message' => 'Holidays synced and saved successfully!']);
    }

}

