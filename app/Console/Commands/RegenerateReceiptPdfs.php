<?php

namespace App\Console\Commands;

use App\Models\Receipt;
use App\Http\Controllers\ReceiptController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateReceiptPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'receipts:regenerate-pdfs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate PDFs for existing receipts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Regenerating PDFs for existing receipts...');
        
        $receipts = Receipt::all();
        $controller = new ReceiptController();
        
        $bar = $this->output->createProgressBar($receipts->count());
        $bar->start();
        
        foreach ($receipts as $receipt) {
            if (!$receipt->pdf_path || !Storage::disk('public')->exists($receipt->pdf_path)) {
                try {
                    $controller->generatePdf($receipt);
                    $this->line("\nRegenerated PDF for receipt: " . $receipt->receipt_number);
                } catch (\Exception $e) {
                    $this->error("\nFailed to regenerate PDF for receipt: " . $receipt->receipt_number . " - " . $e->getMessage());
                }
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('PDF regeneration completed!');
        
        return 0;
    }
} 