<?php

use App\Models\BrandMst;
use App\Models\StockMst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

if (!function_exists('generateBarcode')) {
    function generateBarcode($brandId, $categoryId)
    {
        // Find the total count of existing barcodes for the given brand and category
        $existingCount = StockMst::where('brand_id', $brandId)
            ->where('category_id', $categoryId)
            ->count();

        // Increment the product ID sequentially
        $nextProductId = $existingCount + 1;
        $categories = BrandMst::where("id", $brandId)->with("categories")->first()->categories;

        $catNo = 0;
        foreach ($categories as $category) {
            $catNo++;
            if ($category->id == $categoryId) {
                break;
            }
        }


        // Generate the initial barcode
        $barcode = sprintf('NK-%03d-%03d-%06d', $brandId, $catNo, $nextProductId);

        // Log for debugging
        Log::info('Generated Barcode:', [
            'barcode' => $barcode,
            'brandId' => $brandId,
            'categoryId' => $categoryId,
            'maxProductId' => $existingCount,
            'nextProductId' => $nextProductId
        ]);

        return $barcode;
    }
}




if (!function_exists('generateToken')) {
    function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}