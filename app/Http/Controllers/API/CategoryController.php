<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BrandMst;
use App\Models\CategoryMst;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function createCategory(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string',
            'brand_id' => 'required|integer',
            'image' => 'required|image|max:2048', // Validate the image file
        ]);

        // Store the image file
        $imagePath = $request->file('image')->store('public/category-images');        

        // Create the category
        $category = new CategoryMst();
        $category->name = $validatedData['name'];
        $category->brand_id = $validatedData['brand_id'];
        $category->img = str_replace("public", "public/storage",$imagePath);
        $category->created_by = $request->admin_id;
        try {
            $category->save();
            return response()->json([
                'status' => true,
                'data' => $category,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'data' => $e->getMessage(),
            ]);
        }
    }

    public function getCategoriesById(Request $request, $id)
    {
        $brand = BrandMst::where("id", $id)->where("enabled", true)->first();
        if ($brand != null) {
            $categories = CategoryMst::where("brand_id", $id)->where("enabled", true)->with("admin")->get();
            return response()->json([
                "status" => true,
                "data" => $categories
            ]);
        } else {
            return response()->json([
                "status" => false,
                "data" => "Brand Not Found"
            ]);
        }
    }
    
    public function getCategories(Request $request)
    {           
        
        $categories = CategoryMst::with("brand")->with("admin")->get();

        if (count($categories) > 0 ) {
            return response()->json([
                "status" => true,
                "data" => $categories
            ]);
        } else {
            return response()->json([
                "status" => false,
                "data" => "Brand Not Found"
            ]);
        }
    }

}
