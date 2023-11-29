<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // Extract 'from' and 'to' dates from the request
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
    
         // Fetch orders within the specified date range
         $orders = Order::whereBetween('created_at', [$fromDate, $toDate])->get();
    
         // Calculate order statistics
         $totalCount = $orders->count();
         $totalCommissionOwed = $orders->sum('commission_owed');
         $totalRevenue = $orders->sum('subtotal');
    
        return response()->json([
            'count' => $totalCount,
            'commission_owed' => $totalCommissionOwed,
            'revenue' => $totalRevenue,
        ]);
    }
}
