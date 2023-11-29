<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        // Generate a unique discount code for an affiliate
        $code = Str::uuid();
    
        // Store the generated discount code in the database
        $merchant->discountCodes()->create([
            'code' => $code,
        ]);
    
        return [
            'id' => rand(0, 100000),
            'code' => $code,
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount)
    {
        // Simulate sending a payout, throw an exception if unsuccessful
        if ($this->simulateSendPayout($email, $amount)) {
            throw new RuntimeException('Payout failed');
        }
    }

    /**
     * Simulate sending a payout.
     *
     * @param  string $email
     * @param  float $amount
     * @return bool
     */
    protected function simulateSendPayout(string $email, float $amount): bool
    {
        // Simulate the payout sending process (replace this with actual logic)
        // Return true if successful, false otherwise
        return true;
    }
}
