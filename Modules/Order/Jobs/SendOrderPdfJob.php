<?php

namespace Modules\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderPdfGenerator;
use Modules\Order\Services\WhatsAppService;

class SendOrderPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [30, 60, 120]; // Wait 30s, then 60s, then 120s between retries

    /**
     * The order instance.
     *
     * @var Order
     */
    protected $order;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @param OrderPdfGenerator $pdfGenerator
     * @param WhatsAppService $whatsAppService
     * @return void
     */
    public function handle(OrderPdfGenerator $pdfGenerator, WhatsAppService $whatsAppService)
    {
        try {
            Log::info("Processing WhatsApp notification for order: " . $this->order->getOrderNumber());
            
            // Get the store information using the store_id
            $storeId = $this->order->getStoreId();
            $store = app('\App\Models\Store')::find($storeId);
            
            // If store is not found, log an error and fail the job
            if (!$store) {
                Log::error("Store not found for order: " . $this->order->getOrderNumber() . " (Store ID: {$storeId})");
                $this->fail(new \Exception("Store not found for order: " . $this->order->getOrderNumber()));
                return;
            }
            
            // Get the store phone number
            $storePhone = $store->phone_number ?? config('services.whatsapp.default_phone');
            
            // If store phone is not available, log an error and fail the job
            if (!$storePhone) {
                Log::error("Store phone number not available for order: " . $this->order->getOrderNumber());
                $this->fail(new \Exception("Store phone number not available for order: " . $this->order->getOrderNumber()));
                return;
            }
            
            // Generate the PDF
            $pdfPath = $pdfGenerator->generatePdf($this->order);
            
            // Prepare the message
            $message = "طلب جديد #{$this->order->getOrderNumber()}\n";
            $message .= "المبلغ الإجمالي: " . number_format($this->order->getTotalAmount(), 2) . " ريال\n";
            $message .= "تاريخ الطلب: " . $this->order->getCreatedAt()->format('Y-m-d H:i:s') . "\n";
            $message .= "حالة الطلب: " . $this->order->getStatus() . "\n";
            $message .= "مرفق تفاصيل الطلب في ملف PDF.";
            
            // Send the PDF to WhatsApp
            $success = $whatsAppService->sendPdf($storePhone, $message, $pdfPath);
            
            if ($success) {
                Log::info("Order PDF sent to WhatsApp for order: " . $this->order->getOrderNumber());
            } else {
                throw new \Exception("Failed to send WhatsApp message for order: " . $this->order->getOrderNumber());
            }
        } catch (\Exception $e) {
            Log::error("Error sending order PDF to WhatsApp: " . $e->getMessage());
            
            // If we've exceeded the maximum retry attempts, log a final error
            if ($this->attempts() >= $this->tries) {
                Log::critical("Failed to send WhatsApp notification after {$this->tries} attempts for order: " . $this->order->getOrderNumber());
            }
            
            // Rethrow the exception to trigger a retry (if attempts < tries)
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("WhatsApp notification job failed for order: " . $this->order->getOrderNumber(), [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Here you could implement additional failure handling
        // For example, sending an alert to administrators or triggering a fallback notification
    }
}
