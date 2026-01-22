<?php

namespace App\Http\Controllers\api;

use App\Models\License;
use App\Models\Product;
use App\Models\ProductVersions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function checkUpdate(Request $request)
    {
        $request->validate([
            "item_id" => "required|size:8",
            "version" => "required",
            "initial" => "nullable|boolean"
        ]);

        $product_version = ProductVersions::where('pid', $request->item_id);

        if (!$product_version->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ]);
        }

        if ($request->boolean('initial')) {
            $version = $product_version->latest()->first();
        } else {
            $version = $product_version->where('version', '>', $request->version)
                ->orderBy('version', 'asc')
                ->first();
        }

        if (!$version) {
            return response()->json([
                'success' => true,
                'message' => 'No updates available',
                'data' => [
                    'current_version' => $request->version,
                    'latest_version' => $request->version
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Update available',
            'data' => [
                'current_version' => $request->version,
                'latest_version' => $version->version,
                'updated_id' => $version->vid,
                "has_sql_update" => (bool) $version->sql_file,
                "release_date" => $version->release_date,
                "changelog" => $version->changelog,
                "summary" => $version->summary
            ]
        ]);
    }

    public function downloadSql(Request $request, $vid)
    {
        $request->validate([
            "client_name" => "required|min:2|max:30",
            "purchase_code" => "required|uuid",
            "domain" => "required|url"
        ]);

        $license = License::where('purchase_code', $request->purchase_code)->where('activated_domain', $request->domain)->first();
        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License not found',
            ]);
        }

        $version = ProductVersions::where('vid', $vid)->first();
        if (!$version) {
            return response()->json([
                'success' => false,
                'message' => 'Version not found',
            ]);
        }

        if ($license->item_id != $version->pid || $license->installed_version != $version->version) {
            return response()->json([
                'success' => false,
                'message' => 'license version and version specification not matched',
            ]);
        }

        $filePath = storage_path('app/public/sql/' . $version->sql_file);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                "message" => "File not found"
            ]);
        }

        return response()->download($filePath, $version->sql_file, [
            'Content-Type' => 'text/plain',
        ]);

    }

    public function downloadMain(Request $request, $vid)
    {
        $request->validate([
            "client_name" => "required|min:2|max:30",
            "purchase_code" => "required|uuid",
            "domain" => "required|url"
        ]);

        $license = License::where('purchase_code', $request->purchase_code)->where('activated_domain', $request->domain)->first();
        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'License not found',
            ]);
        }

        $version = ProductVersions::where('vid', $vid)->first();
        if (!$version) {
            return response()->json([
                'success' => false,
                'message' => 'Version not found',
            ]);
        }

        if ($license->item_id != $version->pid || $license->installed_version != $version->version) {
            return response()->json([
                'success' => false,
                'message' => 'license version and version specification not matched',
            ]);
        }

        $filePath = storage_path('app/public/main/' . $version->main_file);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                "message" => "File not found"
            ]);
        }

        return response()->download($filePath, $version->main_file, [
            'Content-Type' => 'text/plain',
        ]);

    }

    public function products($product = null)
    {
        if ($product) {
            $productData = Product::find($product);

            if (!$productData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not found'
                ]);
            }

            if ($productData->license_update) {
                $productData->load('versions');
            }

            $result = $productData;
        } else {
            $result = Product::with([
                'versions' => function ($query) {
                    $query->latest()->first();
                }
            ])->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'product fetch successfully',
            'data' => [
                'product' => $result,
            ]
        ]);
    }

}
