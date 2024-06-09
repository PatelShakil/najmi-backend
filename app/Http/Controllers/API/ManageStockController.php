<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ManageStockController extends Controller
{
    //

        public function generatePdfWithBarcodes(Request $request)
        {
        $barcodes = $request->input('barcodes', []);
            
            $generator = new BarcodeGeneratorPNG();
            $barcodeImages = [];
            foreach ($barcodes as $barcode) {
                $br = base64_encode($generator->getBarcode($barcode,$generator::TYPE_CODE_128));
                $barcodeImages[] = '<img src="data:image/png;base64,' . $br . '" alt="barcode" />';
            }
   
            $pdf = Pdf::loadView('barcodes', ['barcodeImages' => $barcodeImages]);
            return $pdf->download('barcodes.pdf');
        }
}
