<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return Order|null
     */
    public function processOrder(array $data): ?Order
    {
        // Check if there is an existing affiliate for the customer_email
        $affiliate = Affiliate::whereHas('user', function ($query) use ($data) {
            $query->where('email', $data['customer_email']);
        })->first();
    
        // If no affiliate exists, create a new one
        if (!$affiliate) {
            $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
        }
    
        // Check for duplicate orders based on order_id
        if (!Order::where('external_order_id', $data['order_id'])->exists()) {
            // Create a new order and associate it with the affiliate
            $order = Order::create([
                'merchant_id' => $merchant->id, // Corrected: Use $merchant from the outer scope
                'affiliate_id' => $affiliate->id,
                'subtotal' => $data['subtotal_price'],
                'commissions_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
                'payout_status' => Order::STATUS_UNPAID,
                'discount_code' => $data['discount_code'],
                'external_order_id' => $data['order_id'],
            ]);
    
            return $order;
        }
    
        // If a duplicate order exists, return null or handle accordingly
        return null;
    }
}
