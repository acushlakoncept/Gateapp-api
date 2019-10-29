<?php

namespace App\Http\Controllers;
use App\Category;
use App\Home;
use Illuminate\Http\Request;
use App\Service_Provider;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ImageController;



// $query = DB::table('bookings')
//                 ->join('users','bookings.user_id','=','users.id' )
//                 ->join('rooms','bookings.rooms_id','=','rooms.id' )
// 				->join('customers','bookings.customers_id','=','customers.id' )
// 				->select('bookings.checkin_time as checkin_time', 'bookings.checkout_time as checkout_time', 'rooms.room_code as room_code','users.firstname as cashier_fname','users.lastname as cashier_lname','customers.name as cus_name', 'customers.address as cus_address', 'customers.phone as cus_phone', 'customers.gender as cus_gender');

// 		$query->where('bookings.active', 1);
// 		$query->orderBy('rooms.room_code', 'asc');

class ServiceProviderController extends Controller
{
    public function showAll() {
        $res = array();

        if (Auth::check()) {
           $user = Auth::user();
           $role = $user->role;

           if ($role === "1" || $role === "2" || $role == '0') {
                // $service = Service_Provider::all();
                $query = DB::table('service_providers')
                                ->join('estates', 'service_providers.estate_id', '=', 'estates.id')
                                ->join('sp_category', 'service_providers.category_id', '=', 'sp_category.id')
                                ->select('service_providers.id as id', 'service_providers.name as name', 'service_providers.phone as phone', 'service_providers.description as description', 'estates.estate_name as estate', 'sp_category.title as categroy');

                // $query->orderBy('service_providers.name');
                $service = $query->get();

                if (!$service->isEmpty()) {
                    $res["status"] = 200;
                    $res["message"] = "All service providers.";
                    $res["count"] = $service->count();
                    $res["data"] = $service;
                } else {
                    $res["status"] = 200;
                    $res["message"] = "No service providers registered";
                }
           } else {
               $res['status'] = 401;
               $res['message'] = "You must login as a resident or admin.";
           }
       } else {
        $res['status'] = 401;
        $res['message'] = "You are not logged in.";
       }
        return response()->json($res, $res['status']);
    }


    public function byEstate() {
        $user = Auth::user();
        $user_id = $user->id;
        $estate_id = Home::where('user_id',$user_id)->pluck('estate_id');
        $res = array();

        try {

          $service = Category::with(['service_provider' => function ($query) use ($estate_id) {
        $query->whereIn('estate_id', $estate_id); }])->get();


            if (!$service->isEmpty()) {
                $res['status'] = 200;
                $res['message'] = "Retrieved Service Providers per category";
                $res['data'] = $service;
            } else {
                $res['status'] = 404;
                $res['message'] = "No categories found";
            }
        } catch (Exception $e) {
            $res['status'] = 501;
            $res['message'] = "An error occurred retrieving categories";
        }

        return response()->json($res, $res['status']);
    }


    public function show($id)
    {
        $res = array();

        if (Auth::check()) {
           $user = Auth::user();
           $role = $user->role;

           if ($role === "1" || $role === "2" || $role == '0') {
                // $service = Service_Provider::find($id);
                $query = DB::table('service_providers')
                ->join('estates', 'service_providers.estate_id', '=', 'estates.id')
                ->join('sp_category', 'service_providers.category_id', '=', 'sp_category.id')
                ->where('service_providers.id', '=', $id)
                ->select('service_providers.id as id', 'service_providers.name as name', 'service_providers.phone as phone', 'service_providers.description as description', 'estates.estate_name as estate', 'sp_category.title as categroy');

                $service = $query->get();

                if (!is_null($service)) {
                    $res["status"] = 200;
                    $res["message"] = "Service provider found.";
                    $res["data"] = $service;
                } else {
                    $res["status"] = 200;
                    $res["message"] = "No service provider found.";
                }

            } else {
                $res['status'] = 401;
                $res['message'] = "You must login as a resident or admin.";
            }
        } else {
            $res['status'] = 401;
            $res['message'] = "You are not logged in.";
        }
        return response()->json($res, $res['status']);
    }


    public function byCategory($category_id) {
        $res = array();

        if (Auth::check()) {
            $user = Auth::user();
            $role = $user->role;

            if ($role === "1" || $role === "2") {
                try {
                    $services = Service_Provider::where('category_id', $category_id)->get();

                    if(!$services->isEmpty()) {
                        $res['status'] = 200;
                        $res['message'] = "Retrieved service providers";
                        $res['data'] = $services;

                    } else {
                        $res['status'] = 404;
                        $res['message'] = "No service providers in this category";
                    }
                } catch(Exception $e) {
                    $res['status'] = 501;
                    $res['message'] = "An error occurred trying to retrieve service providers $e";
                }
            } else {
                $res['status'] = 401;
                $res['message'] = "You must login as a resident or admin.";
            }
        } else {
            $res['status'] = 401;
            $res['message'] = "You are not logged in";
        }
        return response()->json($res, $res['status']);
    }

    public function create(Request $request, ImageController $image)
    {
         $validator = Validator::make($request->all(), [
               'name'        => 'required|string|min:3',
               'phone'       => 'required',
               'description' => 'required',
               'estate_id'   => 'required|int',
               'category_id' => 'required|int'
          ]);

        if ($validator->fails()) {
        return ['message' => 'Please fill all Fields'];
        }
        //start temporay transaction
        DB::beginTransaction();
        try{

            $service              = new Service_Provider;
            $service->name        = $request->input("name");
            $service->phone       = $request->input("phone");
            $service->description = $request->input("description");
            $service->estate_id   = $request->input("estate_id");
            $service->category_id = $request->input("category_id");

            //Upload image
            //Upload image
            if($request->hasFile('image')) {
                $data = $this->upload($request, $image);
                if($data['status_code'] !=  200) {
                    return response()->json($data, $data['status_code']);
                }
                $service->image = $data['image'];
            }else {
                $data = null;
                $service->image = 'noimage.jpg';
            }
;
            $service->save();

            //if operation was successful save commit save to database
            DB::commit();
            $res["status"] = true;
            $res["message"] = "Service Provider created";
            $res["data"] = $service;
            $res['image_info'] = $data;
            return response()->json($res, 200);

        }catch(\Exception $e) {
            //rollback what is saved
            DB::rollBack();

            $res['status'] = false;
            $res['message'] = 'An error occured, please try again!';
            $res['hint'] = $e->getMessage();
            return response()->json($res, 501);

        }

    }

     public function update(Request $request, $id, ImageController $image)
    {
        $this->validate($request, [
            'name'        => 'required|string|min:3',
            'phone'       => 'required',
            'description' => 'required',
            'estate_id'   => 'required|int',
            'category_id' => 'required|int'
        ]);

        //start temporay transaction
        DB::beginTransaction();
        try{
            $service              = Service_Provider::find($id);
            $service->name        = $request->input("name");
            $service->phone       = $request->input("phone");
            $service->description = $request->input("description");
            $service->estate_id   = $request->input("estate_id");
            $service->category_id = $request->input("category_id");

              //Upload image
            if($request->hasFile('image')) {
                $data = $this->upload($request, $image, $service);
                if($data['status_code'] !=  200) {
                    return response()->json($data, $data['status_code']);
                }
                $visitor->image = $data['image'];
            }else {
                $data = null;
                $visitor->image = 'noimage.jpg';
            }

             $service->save();

             //if operation was successful save commit save to database
            DB::commit();
            $res["status"]  = true;
            $res["message"] = "Service provider Updated Successfully!";
            $res["service"] = $service;
            $res['image_info']   = $data;
            return response()->json($res, 200);
        }catch(\Exception $e) {
            //rollback what is saved
            DB::rollBack();

            $res['status'] = false;
            $res['message'] = 'An error occured, please try again!';
            $res['hint'] = $e->getMessage();
            return response()->json($res, 501);

        }
    }

    public function destroy($id)
    {
        $service = Service_Provider::destroy($id);

        if($service){
            $res['status'] = 200;
            $res["message"] = "Service Provider Deleted!";
            return response()->json($res, 200);
        }else{
            $res['status'] = 404;
            $res["message"] = "No service found";
            return response()->json($res, $res['status']);
        }
    }
    public function upload($request, $image, $table=null) {
        $user = Auth::user();

        $this->validate($request, [
         'image' => "image|max:4000",
        ]);
        //Image Engine
        $res = $image->imageUpload($request, $table);
        return $res;
    }
}
