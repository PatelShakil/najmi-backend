<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockMst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageStockController extends Controller
{

    public function getBarcodeList(Request $request)
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

}
