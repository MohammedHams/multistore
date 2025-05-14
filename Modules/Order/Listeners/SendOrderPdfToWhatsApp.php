<?php

namespace Modules\Order\Listeners;

use Modules\Order\Events\OrderCreated;
use Modules\Order\Jobs\SendOrderPdfJob;
use Modules\Order\Services\OrderPdfGenerator;
use Modules\Order\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SendOrderPdfToWhatsApp
{
    /**
     * The PDF generator instance.
     *
     * @var OrderPdfGenerator
     */
    protected $pdfGenerator;

    /**
     * The WhatsApp service instance.
     *
     * @var WhatsAppService
     */
    protected $whatsAppService;

    /**
     * Create the event listener.
     *
     * @param OrderPdfGenerator $pdfGenerator
     * @param WhatsAppService $whatsAppService
     * @return void
     */
    public function __construct(OrderPdfGenerator $pdfGenerator, WhatsAppService $whatsAppService)
    {
        $this->pdfGenerator = $pdfGenerator;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Handle the event.
     *
     * @param OrderCreated $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        try {
            $order = $event->order;
            
            // Log that we're dispatching the job
            Log::info("Dispatching WhatsApp notification job for order: " . $order->getOrderNumber());
            
            // Dispatch the job to the 'whatsapp' queue with a small delay to ensure the order is fully committed to the database
            SendOrderPdfJob::dispatch($order)
                ->onQueue('whatsapp')
                ->delay(now()->addSeconds(5));
            
            Log::info("WhatsApp notification job dispatched for order: " . $order->getOrderNumber());
        } catch (\Exception $e) {
            Log::error("Error dispatching WhatsApp notification job: " . $e->getMessage());
        }
    }
}
