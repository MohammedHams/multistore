<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class OrderPdfGenerator
{
    /**
     * Generate a PDF for an order
     *
     * @param Order $order
     * @return string The path to the generated PDF file
     */
    public function generatePdf(Order $order): string
    {
        // Create a new DOMPDF instance
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Generate HTML content for the PDF
        $html = $this->generateHtml($order);
        
        // Load the HTML content
        $dompdf->loadHtml($html);
        
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Render the PDF
        $dompdf->render();
        
        // Generate a unique filename
        $filename = 'order_' . $order->getOrderNumber() . '_' . time() . '.pdf';
        $path = storage_path('app/public/orders/' . $filename);
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        // Save the PDF to a file
        file_put_contents($path, $dompdf->output());
        
        return $path;
    }
    
    /**
     * Generate HTML content for the PDF
     *
     * @param Order $order
     * @return string
     */
    private function generateHtml(Order $order): string
    {
        // Get the store information using the store_id
        $storeId = $order->getStoreId();
        $store = app('\App\Models\Store')::find($storeId);
        
        // Render the Blade template with the order and store data
        return View::make('order::pdf.order', [
            'order' => $order,
            'store' => $store
        ])->render();
    }
}
