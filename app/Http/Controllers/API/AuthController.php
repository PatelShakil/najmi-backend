<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdminMst;
use App\Models\WorkerMst;
use Doctrine\Inflector\Rules\Word;
use Exception;
use Faker\Provider\ar_EG\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:10',
            'password' => 'required|min:8',
        ]);

        if (!$validator->fails()) {
            $phone = $request->phone;
            $password = $request->password;
            $user = AdminMst::where("phone", $phone)->where("password", $password)->where("enabled", true)->get()->first();
            if ($user == null) {
                return response()->json([
                    "status" => false,
                    "data" => "Admin Not Found"
                ]);
            } else {
                return response()->json([
                    "status" => true,
                    "data" => $user
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "data" => $validator->messages()->first()
            ]);
        }
    }

    public function workerLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'pin' => 'required|min:6',
        ]);

        if (!$validator->fails()) {
            $phone = $request->phone;
            $password = $request->pin;
            $user = WorkerMst::with("admin")->where("phone", $phone)->where("pin", $password)->where("enabled", true)->get()->first();
            if ($user == null) {
                return response()->json([
                    "status" => false,
                    "data" => "Worker Not Found"
                    // "data" => $user
                ]);
            } else {
                if ($user->enabled) {
                    if ($user->admin->enabled == true) {
                        return response()->json([
                            "status" => true,
                            "data" => $user
                        ]);
                    } else {
                        return response()->json([
                            "status" => false,
                            "data" => "Admin is disabled"
                        ]);
                    }
                } else {
                    return response()->json([
                        "status" => false,
                        "data" => "Worker is disabled"
                    ]);
                }
            }
        } else {
            return response()->json([
                "status" => false,
                "data" => $validator->messages()
            ], 400);
        }
    }

    public function createWorker(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|min:10',
            'pin' => 'required|min:6',
            'admin_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "data" => $validator->messages()->first()
            ]);
        } else {
            $token = generateToken();

            $admin = AdminMst::find($request->admin_id);
            if ($admin != null && $admin->enabled) {

                $worker = new WorkerMst();
                $worker->name = $request->name;
                $worker->phone = $request->phone;
                $worker->pin = $request->pin;
                $worker->created_by = $request->admin_id;
                $worker->token = $token;
                try {
                    $worker->save();
                    return response()->json([
                        "status" => true,
                        "data" => $worker
                    ]);
                } catch (Exception $e) {
                    return response()->json([
                        "status" => false,
                        "data" => $e->getMessage()
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "data" => "Admin Not Found"
                ]);
            }
        }
    }

    public function getWorkers(Request $request)
    {
        $admin = AdminMst::where("token", $request->header("token"))->get()->first();
        if ($admin != null && $admin->enabled) {
            $workers = WorkerMst::where("created_by", $admin->id)->get();
            if ($workers != null && $workers->count() > 0) {
                return response()->json([
                    "status" => true,
                    "data" => $workers
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "data" => "Workers Not Found"
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "data" => "Admin Not Found or Suspended"
            ]);
        }
    }

    public function updateWorker(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|min:10',
            'pin' => 'required|min:6',
            'enabled' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "data" => $validator->messages()->first()
            ]);
        } else {
            $admin = AdminMst::where("token", $request->header("token"))->get()->first();
            if ($admin != null && $admin->enabled) {
                $worker = WorkerMst::find($id);
                if ($worker != null) {
                    $worker->name = $request->name;
                    $worker->phone = $request->phone;
                    $worker->pin = $request->pin;
                    $worker->enabled = $request->enabled == "true" ? true : false;
                    try {
                        $worker->save();
                        return response()->json([
                            "status" => true,
                            "data" => $worker
                        ]);
                    } catch (Exception $e) {
                        return response()->json([
                            "status" => false,
                            "data" => $e->getMessage()
                        ]);
                    }
                } else {
                    return response()->json([
                        "status" => false,
                        "data" => "Worker Not Found"
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "data" => "Admin Not Found or Suspended"
                ]);
            }
        }
    }
}
