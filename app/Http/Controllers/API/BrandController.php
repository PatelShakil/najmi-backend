<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BrandMst;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    //

    public function addBrand(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'name' =>'required',
            'admin_id' =>'required',
        ]);

        if($validator->fails()){
            return response()->json([
               'status' => false,
                'data' => $validator->messages()->first()
            ]);
        }else{
            $brand = new BrandMst();
            $brand->name = $request->name;
            $brand->created_by = $request->admin_id;
            try{
            $brand->save();
            return response()->json([
               'status' => true,
                'data' => $brand
            ]);
        }catch(Exception $e){
            return response()->json([
               'status' => false,
                'data' => $e->getMessage()
            ]);
        }
        }



    }

    public function getBrands(Request $request) {
        $brands = BrandMst::where("enabled", true)->with("admin")->get();
        if (count($brands) > 0) {
            return response()->json([
                'status' => true,
                'data' => $brands
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => null
            ]);
        }
    }

    public function getBrandsPage(Request $request) {
        $brands = BrandMst::with("admin")->get();
        if (count($brands) > 0) {
            return response()->json([
                'status' => true,
                'data' => $brands
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => null
            ]);
        }
    }



}
