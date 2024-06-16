<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BrandMst;
use App\Models\CategoryMst;
use App\Models\StockMst;
use App\Models\WorkerMst;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageStockController extends Controller
{
    public function getBarcodeList(Request $request)
    {
        $stocks = $request->stocks;
        $barcodes = [];

        // Check if $stocks is empty or not an array
        if (empty($stocks) || !is_array($stocks) || count($stocks) === 0) {
            return response()->json([
                "status" => false,
                "data" => "No stocks provided"
            ]);
        }

        try {
            DB::beginTransaction();

            foreach ($stocks as $stock) {
                $br_no = 0;

                if (BrandMst::where("id", $stock['brand_id'])->where("enabled", true)->exists()) {
                    if (CategoryMst::where("id", $stock['category_id'])->where("enabled", true)->where("brand_id", $stock['brand_id'])->exists()) {
                        // Loop to create stock entries
                        while ($br_no < $stock['quantity']) {
                            $stockDto = new StockMst();
                            $stockDto->barcode_no = generateBarcode($stock['brand_id'], $stock['category_id']); // Assume generateBarcode is a method in the same controller
                            $stockDto->name = $stock['name'];
                            $stockDto->brand_id = $stock['brand_id'];
                            $stockDto->category_id = $stock['category_id'];
                            $stockDto->mrp = $stock['mrp'];
                            $stockDto->created_by = $stock['created_by'];
                            // Assuming color_id is optional, otherwise include validation above
                            if (isset($stock['color_id'])) {
                                $stockDto->color_id = $stock['color_id'];
                            }
                            $stockDto->save();
                            $barcodes[] = $stockDto;
                            $br_no++;
                        }
                    } else {
                        DB::rollBack();
                        return response()->json([
                            "status" => false,
                            "data" => "Category not found"
                        ]);
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        "status" => false,
                        "data" => "Brand not found"
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                "status" => true,
                "data" => $barcodes,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            // Optionally log the exception here
            return response()->json([
                "status" => false,
                "data" => $e->getMessage()
            ]);
        }
    }

    public function getStock(Request $request)
    {
        $stocks = BrandMst::where("enabled", true)->get();

        if (count($stocks) > 0) {
            return response()->json([
                'status' => true,
                'data' => $stocks
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => null
            ]);
        }
    }



    public function getStockDetails(Request $request, $br)
    {
        if (StockMst::where("barcode_no", $br)->exists()) {
            $stock = StockMst::where('barcode_no', $br)
                ->with("admin")
                ->with("brand")
                ->with("category")
                ->with("color")
                ->with("worker")->first();

            if ($stock->worker == null) {
                return response()->json([
                    'status' => true,
                    'data' => $stock
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => null
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'data' => null
            ]);
        }
    }

    public function saleStock(Request $request, $br)
    {

        $worker = WorkerMst::where("token", $request->header("token"))->first();

        if ($worker != null && $worker->enabled) {
            if (StockMst::where("barcode_no", $br)->exists()) {
                $stock = StockMst::where('barcode_no', $br)->get()->first();
                if ($stock->enabled) {
                    if (!$stock->is_sold) {
                        $stock->is_sold = true;
                        $stock->sold_by = $worker->id;
                        $stock->save();
                        return response()->json([
                            'status' => true,
                            'data' => $br + " Sold Successfully"
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'data' => $br + " Already Sold"
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'data' => $br + " is Disable"
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'data' => "Stock Not Found"
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'data' => "Worker Not Found"
            ]);
        }
    }
}
