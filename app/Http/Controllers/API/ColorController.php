<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ColorMst;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ColorController extends Controller
{
    //

    public function addColor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required',
            'admin_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => $validator->messages()->first()
            ]);
        }

        $color = new \App\Models\ColorMst();
        $color->name = $request->name;
        $color->code = $request->code;
        $color->created_by = $request->admin_id;
        try {
            $color->save();
            return response()->json(['status' => true, 'data' => $color]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'data' => $e->getMessage()]);
        }
    }

    public function getColors(Request $request){
        $colors = ColorMst::where('enabled', true)->with("admin")->get();
        return response()->json([
            'status' => true,
            'data' => $colors
        ]);
    }


}
