<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
{
    // Generate a unique API key for the merchant
    $api_key = $this->generateUniqueApiKey();

    // Create a new user and check if it was successful
    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => bcrypt($api_key),
        'type' => User::TYPE_MERCHANT,
    ]);

    if (!$user) {
        // Handle the case where user creation failed
        // You might want to throw an exception or log an error
        // For simplicity, let's assume throwing an exception for now
        throw new \RuntimeException('User creation failed');
    }

    // Now that we have the user, create the associated merchant
    $merchant = Merchant::create([
        'user_id' => $user->id,
        'domain' => $data['domain'],
        'display_name' => $data['name'],
    ]);

    return $merchant;
}

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        // Update the associated merchant's details
        $user->merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Find the user with the given email
        $user = User::where('email', $email)->first();
        
        // Return the associated merchant if the user exists
        return $user ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */

    public function payout(Affiliate $affiliate)
    {
        // Retrieve unpaid orders for the affiliate
        $unpaidOrders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();

        // Dispatch a job for each unpaid order
        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }
    /**
     * Generate a unique API key for the merchant.
     *
     * @return string
     */
    protected function generateUniqueApiKey(): string
    {
        // Use Laravel's Str::uuid() to generate a unique API key
        return \Illuminate\Support\Str::uuid();
    }
}
