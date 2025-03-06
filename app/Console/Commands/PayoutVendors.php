<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Vendor;
use App\Mail\VendorPayout;
use App\Enums\OrderStatusEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayoutVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout:vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform vendors payout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Log the start of the payout process
        $this->info('Starting monthly payout process for vendors...');

        // Get vendors eligible for payout and has active Stripe account
        $vendors  = Vendor::eligibleForPayout()->get();

        // Process payout for each vendor
        foreach ($vendors as $vendor) {
            $subtotal = $this->processPayout($vendor);
            $subtotal = Number::currency($subtotal);

            Mail::to($vendor->with('user')->email)->queue(new VendorPayout($vendor, $subtotal));
        }

        // Log the end of the payout process
        $this->info('Monthly payout process completed.');

        return Command::SUCCESS;
    }

    // -----------------------------------------------
    /**
     * 
     *   ██   ██ ███████ ██      ██████  ███████ ██████  
     *   ██   ██ ██      ██      ██   ██ ██      ██   ██ 
     *   ███████ █████   ██      ██████  █████   ██████  
     *   ██   ██ ██      ██      ██      ██      ██   ██ 
     *   ██   ██ ███████ ███████ ██      ███████ ██   ██ 
     *
     */

    /**
     * Process payout for vendor user
     * 
     * @param \App\Models\Vendor $vendor
     * @return int
     */
    protected function processPayout(Vendor $vendor): int
    {
        // Store subtotal amount earned by vendor
        $subtotal = 0;

        // Log the start of payout process of a specific vendor
        $this->info("Processing payout for vendor [ID=$vendor->user_id] - \"$vendor->store_name\"");

        try {
            DB::beginTransaction();

            $start_date = Payout::where('vendor_id', $vendor->user_id)
                                        ->orderBy('end_date', 'desc') // Payouts with the latest 'end_date' will appear first.
                                        ->value('end_date'); // Retrieves the value of the 'end_date' column from the first row of the sorted results.

            /**
             * If previous payout date is null, then this is the first payout for the vendor user
             * If so, assign the date from Unix epoch
             */
            $start_date = $start_date ?: Carbon::make('1970-01-01');

            /**
             * Calculate when the next payout date is for this vendor user
             * 
             * For example,
             * 
             *  The date today is March 2, 2025.
             *  The code below will convert that date to the previous month which is February 2, 2025 with the time 00:00:00 (midnight)
             *  followed by a conversion to the first day of that previous month which is February 1, 2025
             * 
             *  Note: no overflow here helps Carbon to properly subtract the given date to the previous month
             */
            $end_date = Carbon::now()->subMonthNoOverflow()->startOfMonth();

            // Calculate the total payout to be received by vendor
            $vendorSubtotal = Order::where('vendor_user_id', $vendor->user_id)
                                    ->where('status', OrderStatusEnum::Paid->value)
                                    ->whereBetween('created_at', [$start_date, $end_date])
                                    ->sum('vendor_subtotal');

            // Log the amount of vendor subtotal
            if ($vendorSubtotal) {
                $this->info('Payout made with amount: ' . $vendorSubtotal);

                // Create a payout record
                Payout::create([
                    'vendor_id' => $vendor->user_id,
                    'amount' => $vendorSubtotal,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ]);

                // Store the subtotal
                $subtotal =  $vendorSubtotal;

                /**
                 * This method comes from the installed 'laravel stripe connect' package
                 * 
                 * Note: Multiply 100 to be in US cent unit
                 */
                $vendor->user->transfer((int)($vendorSubtotal * 100), config('app.currency'));

            } else {
                $this->info('Nothing to process...');
            }

            DB::commit();
        } catch (\Exception $e) {

            // Revert the DB transaction
            DB::rollback();

            // Log the error message
            $this->error($e->getMessage());
        }

        return $subtotal;
    }
}
