<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class JsonCompareController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function store(Request $request)
    {
        Log::info('Received store request', ['data' => $request->all()]);

        try {
            $request->validate([
                'number' => 'required|integer|between:1,2',
                'payload_to_compare' => 'required|json'
            ]);

            $number = $request->input('number');
            $payload = json_decode($request->input('payload_to_compare'), true);

            // Store the payload in a file
            Storage::put("payload_{$number}.json", json_encode($payload, JSON_PRETTY_PRINT));

            return response()->json([
                'message' => "Payload {$number} stored successfully"
            ]);
        } catch (\Exception $e) {
            Log::error('Error in store method', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function compare()
    {
        try {
            // Check if both files exist
            if (!Storage::exists('payload_1.json') || !Storage::exists('payload_2.json')) {
                return response()->json([
                    'error' => 'Both payloads must be stored before comparison'
                ], 400);
            }

            // Read both payloads
            $payload1 = json_decode(Storage::get('payload_1.json'), true);
            $payload2 = json_decode(Storage::get('payload_2.json'), true);

            // Compare the payloads
            $differences = $this->findDifferences($payload1, $payload2);

            // Delete both files after comparison
            Storage::delete(['payload_1.json', 'payload_2.json']);

            return response()->json([
                'differences' => $differences,
                'message' => 'Comparison completed and payloads deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in compare method', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    private function findDifferences($array1, $array2, $path = '')
    {
        $differences = [];

        foreach ($array1 as $key => $value1) {
            $currentPath = $path ? "{$path}.{$key}" : $key;

            if (!array_key_exists($key, $array2)) {
                $differences[] = [
                    'path' => $currentPath,
                    'type' => 'missing_in_second',
                    'value' => $value1
                ];
                continue;
            }

            $value2 = $array2[$key];

            if (is_array($value1) && is_array($value2)) {
                $nested_differences = $this->findDifferences($value1, $value2, $currentPath);
                $differences = array_merge($differences, $nested_differences);
            } elseif ($value1 !== $value2) {
                $differences[] = [
                    'path' => $currentPath,
                    'type' => 'value_mismatch',
                    'value1' => $value1,
                    'value2' => $value2
                ];
            }
        }

        // Check for keys in array2 that don't exist in array1
        foreach ($array2 as $key => $value2) {
            if (!array_key_exists($key, $array1)) {
                $currentPath = $path ? "{$path}.{$key}" : $key;
                $differences[] = [
                    'path' => $currentPath,
                    'type' => 'missing_in_first',
                    'value' => $value2
                ];
            }
        }

        return $differences;
    }
}
