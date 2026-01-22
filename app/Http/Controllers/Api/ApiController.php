<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiActivities;
use App\Models\ApiRequest;
use App\Models\BlockedIps;
use Illuminate\Http\Request;


class ApiController extends Controller
{

    public function apiRequests(Request $request)
    {
        $request->validate([
            "purchase_code" => "required|uuid",
            "domain" => "required|url",
        ]);

        $requests = ApiRequest::where('purchase_code', $request->purchase_code)->where('domain', $request->domain)->get();
        if (count($requests) == 0) {
            return response()->json([
                'success' => false,
                'message' => "No api request found"
            ]);

        }
        return response()->json([
            'success' => true,
            'message' => 'request fetch successfully.',
            'data' => [
                'requests' => $requests
            ]
        ]);
    }

    public function apiActivities(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "purchase_code" => "required|uuid",
            "domain" => "required|url"
        ]);

        $query = ApiActivities::where('item_id', $request->item_id)
            ->where('purchase_code', $request->purchase_code);

        if ($request->event_type) {
            $query->where('event_type', $request->event_type);
        }


        $activities = $query->get();

        if ($activities->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "No api activity found"
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "api activity fetch successfully",
            "data" => [
                "activities" => $activities
            ]
        ]);
    }

    public function blockedIps(Request $request, $ip = null)
    {
        $query = BlockedIps::query();

        if ($ip) {
            $query->where('id', $ip);
        }

        if ($request->block_type) {
            $query->where('block_type', $request->block_type);
        }
        $ips = $query->get();

        return response()->json([
            "success" => true,
            "message" => "ip fetch successfully",
            "data" => [
                "blocked_ips" => $ips
            ]
        ]);
    }
}