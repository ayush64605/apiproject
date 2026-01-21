<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\License;
use Jenssegers\Agent\Agent;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "purchase_code" => "required|uuid",
            "purchase_time" => "required|date_format:Y-m-d\TH:i:sP",
            "buyer" => "required|min:2|max:30",
            "activated_domain" => "required|url",
            "license" => "required|in:Regular License, Extended License",
            "purchase_count" => "required|numeric",
        ]);

        $exist = License::where('purchase_code', $request->purchase_code)->first();
        if ($exist) {
            return response()->json([
                "success" => false,
                "message" => "Invalid purchase code",
                "error_code" => "TOKEN_INVALID"
            ], 400);
        }

        $product = Product::where('item_id', $request->item_id)->first();
        if (!$product) {
            return response()->json([
                "success" => false,
                "message" => "Invalid Item id",
                "error_code" => "ITEM_ID_INVALID"
            ], 400);
        }

        $agent = new Agent();

        $license = new License();
        $license->item_id = $request->item_id;
        $license->item_name = $product->name;
        $license->purchase_code = $request->purchase_code;
        $license->purchase_time = $request->purchase_time;
        $license->purchase_time = $request->purchase_time;
        $license->buyer = $request->buyer;
        $license->activated_domain = $request->activated_domain;
        $license->license = $request->license;
        $license->purchase_count = $request->purchase_count;
        $license->ip = $request->ip();
        $license->user_agent = $request->userAgent();
        $license->os = $agent->platform();
        $license->save();
        return response()->json([
            "success" => true,
            "message" => "License registered successfully",
            "data" => [
                "verification_id" => "$request->item_id|$license->id|$request->buyer|$request->purchase_code",
            ]
        ], 200);
    }

    public function validate(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "purchase_code" => "required|uuid",
            "activated_domain" => "required|url",
        ]);

        $license = License::where("item_id", $request->item_id)->where('purchase_code', $request->purchase_code)->first();
        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Request Not Found',
            ], 400);
        }

        $license->last_validate_request = Carbon::now();
        $license->save();
        return response()->json([
            'success' => true,
            'message' => 'License Validated Sucessfully',
        ], 200);
    }

    public function getDomain(Request $request)
    {
        $request->validate([
            'purchase_code' => 'required|uuid',
        ]);

        $license = License::where('purchase_code', $request->purchase_code)->first();
        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License not found',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'License found sucessfully',
            "data" => [
                "activated_domain" => $license->activated_domain,
            ]
        ]);
    }
}