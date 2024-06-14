<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BrandMst;
use App\Models\CategoryMst;
use App\Models\StockMst;
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

        if ($stocks->count() <= 0) {
            return response()->json([
                "status" => false,
                "data" => "Stock not found"
            ]);
        }

        try {
            DB::beginTransaction();

            foreach ($stocks as $stock) {
                $br_no = 0;

                if (BrandMst::where("id", $stock->brand_id)->where("enabled", true)->exists()) {
                    if (CategoryMst::where("id", $stock->category_id)->where("enabled", true)->where("brand_id", $stock->brand_id)->exists()) {
                        while ($br_no < $stock->quantity) {
                            $stockDto = new StockMst();
                            $stockDto->barcode_no = generateBarcode($stock->brand_id, $stock->category_id);
                            $stockDto->name = $stock->name;
                            $stockDto->brand_id = $stock->brand_id;
                            $stockDto->category_id = $stock->category_id;
                            $stockDto->mrp = $stock->mrp;
                            $stockDto->created_by = $stock->created_by;

                            if ($stock->color_id != null) {
                                $stockDto->color_id = $stock->color_id;
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

    public function getBarcodeList2(Request $request)
    {
        $brand_id = $request->brand_id;
        $category_id = $request->category_id;
        $count = $request->quantity;
        $mrp = $request->mrp;
        $admin_id = $request->admin_id;
        $color_id = $request->color_id;
        $name = $request->name;
        $list = [];
        if (BrandMst::where("id", $brand_id)->where("enabled", true)->exists()) {
            if (CategoryMst::where("id", $category_id)->where("enabled", true)->where("brand_id", $brand_id)->exists()) {
                $i = 1;
                while ($i <= $count) {
                    $stock = new StockMst();
                    $stock->barcode_no = generateBarcode($brand_id, $category_id);
                    $stock->name = $name;
                    $stock->brand_id = $brand_id;
                    $stock->category_id = $category_id;
                    $stock->mrp = $mrp;
                    $stock->created_by = $admin_id;
                    if ($color_id != null) {
                        $stock->color_id = $request->color_id;
                    }
                    $stock->save();
                    $list[] = $stock;
                    $i++;
                }
                return response()->json([
                    "status" => true,
                    "data" => $list
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "data" => "Category not found"
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "data" => "Brand not found"
            ]);
        }
    }


    public function getBarcodeList1(Request $request)
    {
        // Logging the start of the function
        Log::info('getBarcodeList started', $request->all());

        // Validation
        // $validated = $request->validate([
        //     'brand_id' => 'required|integer|exists:brands_mst,id,enabled,true',
        //     'category_id' => 'required|integer|exists:categories_mst,id,enabled,true,brand_id,' . $request->brand_id,
        //     'count' => 'required|integer|min:1',
        //     'mrp' => 'required|numeric',
        //     'admin_id' => 'required|integer|exists:admin_mst,id',
        //     'color_id' => 'nullable|integer|exists:colors_mst,id',
        //     'name' => 'required|string|max:255',
        // ]);
        $validated = $request->all();

        Log::info('Request validated successfully');

        $brand_id = $validated['brand_id'];
        $category_id = $validated['category_id'];
        $count = $validated['count'];
        $mrp = $validated['mrp'];
        $admin_id = $validated['admin_id'];
        $color_id = $validated['color_id'];
        $name = $validated['name'];
        $list = [];

        try {
            DB::beginTransaction();

            for ($i = 0; $i < $count; $i++) {
                Log::info('Generating barcode for item', ['iteration' => $i + 1]);

                $stock = new StockMst();
                $stock->barcode_no = generateBarcode($brand_id, $category_id);
                $stock->name = $name;
                $stock->brand_id = $brand_id;
                $stock->category_id = $category_id;
                $stock->mrp = $mrp;
                $stock->created_by = $admin_id;

                if ($color_id) {
                    $stock->color_id = $color_id;
                }

                $stock->save();
                $list[] = $stock->barcode_no;

                Log::info('Barcode generated and saved', ['barcode_no' => $stock->barcode_no]);
            }

            DB::commit();

            Log::info('All barcodes generated and transaction committed');

            return response()->json([
                "status" => true,
                "data" => $list,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('An error occurred', ['message' => $e->getMessage()]);

            return response()->json([
                "status" => false,
                "data" => "An error occurred: " . $e->getMessage(),
            ]);
        }
    }

    public function getStock(Request $request, $br)
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
}
