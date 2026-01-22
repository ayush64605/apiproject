<?php

namespace App\Http\Controllers\api;

use App\Models\BuyerProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuyerController extends Controller
{
    public function buyers($buyer = null)
    {
        $query = BuyerProfile::select('id', 'envato_username', 'email');

        if ($buyer) {
            $data = $query->where('id', $buyer)->first();
        } else {
            $data = $query->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Buyer fetch successfully.',
            'data' => [
                'buyers' => $data
            ]
        ]);
    }
}
