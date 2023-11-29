<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Check if the email is already used by the merchant
        if ($merchant->user->email === $email) {
            throw new AffiliateCreateException("Email is already used by the merchant.");
        }

        // Check if the email is already used by an existing affiliate
        if (Affiliate::where('user_id', '!=', $merchant->user->id)->whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists()) {
            throw new AffiliateCreateException("Email is already used by another affiliate.");
        }

        // Generate a new affiliate user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE,
        ]);

        // Generate a unique discount code
        $discountCode = $this->apiService->createDiscountCode($merchant)['code'];

        // Create a new affiliate record
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode,
        ]);

        // Send an email to the affiliate
        Mail::to($email)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }

    /**
     * Generate a unique discount code.
     *
     * @return string
     */
    private function discCode(): string
    {
        $length = 8;
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $discountCode = '';
        for ($i = 0; $i < $length; $i++) {
            $discountCode .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $discountCode;
    }
}
