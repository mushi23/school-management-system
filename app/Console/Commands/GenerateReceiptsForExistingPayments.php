<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Receipt;

class GenerateReceiptsForExistingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'receipts:generate-for-existing-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate receipts for existing payments that do not have receipts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting receipt generation for existing payments...');

        // Get payments that don't have receipts
        $payments = Payment::whereDoesntHave('receipts')->get();
        
        if ($payments->isEmpty()) {
            $this->info('No payments found without receipts.');
            return 0;
        }

        $this->info("Found {$payments->count()} payments without receipts.");

        $bar = $this->output->createProgressBar($payments->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($payments as $payment) {
            try {
                // Get term from invoice if available
                $term = null;
                $academicYear = date('Y');
                
                if ($payment->invoice) {
                    $term = $payment->invoice->term;
                }

                // Create receipt
                Receipt::createFromPayment($payment, $term, $academicYear);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to generate receipt for payment {$payment->id}: " . $e->getMessage());
                $errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Receipt generation completed!");
        $this->info("Successfully generated: {$successCount} receipts");
        
        if ($errorCount > 0) {
            $this->warn("Failed to generate: {$errorCount} receipts");
        }

        return 0;
    }
}
