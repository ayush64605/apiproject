<?php

namespace App\Http\Controllers\api;

use App\Models\ApiActivities;
use App\Models\License;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Jenssegers\Agent\Agent;

class LicenseController extends Controller
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
                "message" => "License already registered.",
            ]);
        }

        $product = Product::where('item_id', $request->item_id)->first();
        if (!$product) {
            return response()->json([
                "success" => false,
                "message" => "Product not found.",
            ]);
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
        ]);
    }

    public function validate(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "purchase_code" => "required|uuid",
            "activated_domain" => "required|url",
            "version" => "required",
        ]);

        $license = License::where("item_id", $request->item_id)
            ->where('purchase_code', $request->purchase_code)
            ->where('activated_domain', $request->activated_domain)
            ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License not registered.',
            ]);
        }

        $license->last_validate_request = Carbon::now();
        $license->installed_version = $license->availableVersions()->latest()->first()->version;
        $license->save();

        return response()->json([
            'success' => true,
            'message' => 'License Validated Successfully',
        ]);
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
                'message' => 'License not registered.',
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
            ]);
        }

        $lastReset = $license->resetLogs()->latest('id')->first();

        if ($lastReset && $lastReset->reset_license_time->greaterThan(now()->subWeek())) {
            $nextAvailable = $lastReset->reset_license_time->addWeek();
            return response()->json([
                'success' => false,
                'message' => 'You can only reset your license once per week. Next available reset: ' .
                    $nextAvailable->toDateTimeString()
            ]);
        }

        $license->resetLogs()->create([
            'reset_license_time' => now(),
            'type' => 'agent',
            'reset_by' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'License reset successfully.'
        ]);
    }

    public function licenseReport(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8"
        ]);

        $activities = ApiActivities::where('item_id', $request->item_id)->get();

        if ($activities->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No records found'
            ]);
        }

        $purchaseCodes = $activities->pluck('purchase_code')->toArray();

        return response()->json([
            'success' => true,
            'purchase_code' => $purchaseCodes,
            'total_activities_count' => $activities->count(),
            'event_types_count' => $activities->unique('event_type')->count(),
            'unique_domain_count' => $activities->unique('domain')->count(),
            'first_activity' => $activities->min('created_at'),
            'last_activity' => $activities->max('created_at'),
        ]);
    }
}
