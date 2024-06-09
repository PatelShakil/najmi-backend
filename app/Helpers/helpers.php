<?php

use App\Models\BrandMst;
use App\Models\StockMst;
use Illuminate\Support\Facades\DB;



if (!function_exists('generateBarcode')) {
    function generateBarcode($brandId, $categoryId)
    {
        // Fetch the brand abbreviation
        $brand = BrandMst::find($brandId);
        if (!$brand) {
            throw new \Exception("Invalid brand ID");
        }

        // Ensure the brand name is at least 3 characters long, pad if necessary
        $brandAbbr = strtoupper(substr($brand->name, 0, 3));
        $brandAbbr = str_pad($brandAbbr, 3, 'X', STR_PAD_RIGHT);

        // Find the next available product ID for the given brand and category
        $maxProductId = StockMst::where('brand_id', $brandId)
            ->where('category_id', $categoryId)
            ->max(DB::raw('CAST(SUBSTRING(barcode_no, 11, 6) AS UNSIGNED)'));

        $nextProductId = $maxProductId ? $maxProductId + 1 : 1;

        // Generate the initial barcode
        $barcode = 'NK-' . $brandAbbr . '-' . str_pad($categoryId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($nextProductId, 6, '0', STR_PAD_LEFT);

        // Ensure the barcode is unique
        $counter = 1;
        $uniqueBarcode = $barcode;
        while (StockMst::where('barcode_no', $uniqueBarcode)->exists()) {
            $uniqueBarcode = $barcode . '-' . str_pad($counter++, 3, '0', STR_PAD_LEFT);
        }

        return $uniqueBarcode;
    }
}

if (!function_exists('generateToken')) {
    function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}