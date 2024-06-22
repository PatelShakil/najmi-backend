<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BrandMst;
use App\Models\CategoryMst;
use App\Models\StockMst;
use App\Models\WorkerMst;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
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
            if ($stock != null) {
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
                        $stock->sold_at = new DateTime();
                        $stock->save();
                        return response()->json([
                            'status' => true,
                            'data' => $br . " Sold Successfully"
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'data' => $br . " Already Sold"
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'data' => $br . " is Disable"
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

    public function returnProduct(Request $request, $br)
    {
        $stock = StockMst::where("barcode_no", $br)->first();

        if ($stock != null) {
            $stock->is_sold = false;
            $stock->sold_by = null;
            try {
                $stock->save();
                $stock = StockMst::where('barcode_no', $br)
                    ->with("admin")
                    ->with("brand")
                    ->with("category")
                    ->with("color")
                    ->with("worker")->first();

                return response()->json([
                    'status' => true,
                    'data' => $stock
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'data' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'data' => "Stock Not Available"
            ]);
        }
    }

    public function getStockList(Request $request, $c_id)
    {
        $stocks = StockMst::where("category_id", $c_id)
            ->with(["worker", "category", "brand", "admin", "color"])
            ->get();

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
    public function getStockReport(Request $request)
    {
        // Get the current date and the date 30 days ago
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        // Check if the request has custom startDate and endDate
        if ($request->has(['start_date', 'end_date'])) {
            $request->validate([
                'start_date' => 'required|date_format:m/d/Y',
                'end_date' => 'required|date_format:m/d/Y|after_or_equal:start_date',
            ]);
            $startDate = Carbon::createFromFormat('m/d/Y', $request->start_date);
            $endDate = Carbon::createFromFormat('m/d/Y', $request->end_date);
        }

        // Retrieve the data from StockMst where created_at is between startDate and endDate
        $stockData = StockMst::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with(["worker", "category", "brand", "admin", "color"])
            ->get();

        // Return the data as JSON
        return response()->json([
            'status' => true,
            'data' => $stockData
        ]);
    }
}
