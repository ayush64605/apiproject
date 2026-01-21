<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiActivities;
use App\Models\ApiRequest;
use App\Models\BuyerProfile;
use App\Models\Product;
use App\Models\ProductVersions;
use App\Models\ResetLicenseActivityLogs;
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
            "version" => "required|string",
        ]);

        $license = License::where("item_id", $request->item_id)
            ->where('purchase_code', $request->purchase_code)
            ->whereRelation('product.versions', 'version', $request->version)
            ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid license or version mismatch.',
            ], 400);
        }

        $license->last_validate_request = Carbon::now();
        $license->save();

        return response()->json([
            'success' => true,
            'message' => 'License and Version Validated Successfully',
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

    public function checkUpdate(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "purchase_code" => "required|uuid",
            "initial" => "nullable|boolean"
        ]);

        $license = License::where("item_id", $request->item_id)
            ->where('purchase_code', $request->purchase_code)
            ->first();

        if (!$license) {
            return response()->json(['success' => false, 'message' => 'License Not Found'], 400);
        }

        $versionQuery = $license->availableVersions();

        if ($request->boolean('initial')) {
            $version = $versionQuery->latest('version')->first();
        } else {
            $version = $versionQuery->where('version', '>', $license->installed_version)
                ->orderBy('version', 'asc')
                ->first();
        }

        if (!$version) {
            return response()->json([
                'success' => true,
                'message' => 'No updates available',
                'data' => [
                    'current_version' => $license->installed_version,
                    'latest_version' => $license->installed_version
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Update available',
            'data' => [
                'current_version' => $license->installed_version,
                'latest_version' => $version->version,
                "has_sql_update" => $version->sql_file_path,
                "release_date" => $version->release_date,
                "changelog" => $version->changelog,
                "summary" => $version->summary
            ]
        ]);
    }


    public function resetLicense(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "purchase_code" => "required|uuid",
        ]);

        $license = License::where("item_id", $request->item_id)
            ->where('purchase_code', $request->purchase_code)
            ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License not found',
            ], 404);
        }

        $lastReset = $license->resetLogs()->latest('id')->first();

        if ($lastReset && $lastReset->reset_license_time->greaterThan(now()->subWeek())) {
            $nextAvailable = $lastReset->reset_license_time->addWeek();
            return response()->json([
                'success' => false,
                'message' => 'You can only reset your license once per week. Next available reset: ' .
                    $nextAvailable->toDateTimeString()
            ], 403);
        }

        $license->resetLogs()->create([
            'reset_license_time' => now(),
            'type' => 'agent',
            'reset_by' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'License reset successfully.'
        ], 200);
    }

    public function buyers()
    {
        $buyers = BuyerProfile::select('id', 'envato_username', 'email')->get();
        return response()->json([
            'success' => true,
            'buyers' => $buyers
        ]);
    }

    public function buyerdetails($buyer)
    {
        try {
            $buyer = BuyerProfile::findOrFail($buyer);
            return response()->json([
                'success' => true,
                'buyer' => $buyer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error in fetch details"
            ], 500);
        }
    }

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
            'requests' => $requests
        ]);
    }

    public function apiActivities(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "purchase_code" => "required|uuid",
            "domain" => "required|url",
        ]);

        $activities = ApiActivities::where('item_id', $request->item_id)->where('purchase_code', $request->purchase_code)->where('domain', $request->domain)->get();
        if (count($activities) == 0) {
            return response()->json([
                'success' => false,
                'message' => "No api activity found"
            ]);

        }
        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }
}