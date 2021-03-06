<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterAuthRequest;
use App\User;
use App\Person;
use App\Report;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
 
class ApiController extends Controller
{
    public $loginAfterSignUp = true;
 
    public function register(Request $request)
    {
        
        $user =  User::where([
            ['email', '=', $request->email],
        ])->first();

        //dd($user);

        if($user != null){
            $roles = unserialize($user->role);
            //dd($roles);

            if(in_array($request->role, $roles)){
                return response()->json([
                    'success' => false,
                    'message' => "user already exists"
                ], 500);
            }
            else{
                array_push($roles, $request->role);
                $user->role = serialize($roles);
                $user->save();

                return response()->json([
                    'success' => true,
                    'data' => $user
                ], 200);
            }
        }
        else{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|max:10|confirmed',
                'role' => 'required|string',
                'phoneNumber' => '',
                'address' => '',
                'about' => '',
                'additionalFields' => '',
                'credentials' => '',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data' => $validator->errors()
                ], 500);
            }

            $roles = [];
            array_push($roles, $request->role);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->role = serialize($roles);
            $user->email = $request->email;
            $user->phoneNumber = $request->phoneNumber;
            $user->address = serialize($request->address);
            $user->about = $request->about;
            $user->additionalUrls = $request->additionalFields;

            if($user->save()){
                $user->role = unserialize($user->role);
                return response()->json([
                    'success' => true,
                    'data' => $user
                ], 200);

            }
    
            
        }

    }

 
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $jwt_token = null;
 
        if (!$jwt_token = JWTAuth::attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }
 
        return response()->json([
            'success' => true,
            'token' => $jwt_token,
        ]);
    }

    public function createReport(Request $request){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'address' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'tags' => 'required|string',
            'persons' => 'required|string',           
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->errors()
            ], 500);
        }

        $personIDs = [];

        foreach (json_decode($request->persons) as $person) {
            if($person != null){
                $newPerson = new Person();

                $newPerson->name = $person->name;
                $newPerson->email = $person->email;
                $newPerson->phoneNumber = $person->phone;
                $newPerson->address = $person->address;
                $newPerson->type = $person->type;
            
                $newPerson->save();

                array_push($personIDs, $newPerson->id);
            }    
        }

        $report = new Report();
        $report->title = $request->title;
        $report->description = $request->description;
        $report->address = $request->address;
        $report->state = $request->state;
        $report->country = $request->country;
        $report->status = "Pending";
        $report->tags = serialize(json_decode($request->tags));
        $report->personInvolvedIDs = serialize($personIDs);
        
        
        if($report->save()){
            return response()->json([
                'success' => true,
                'data' => $report
            ], 200);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => "error creating report"
            ], 500);
        }
  
    }
 
}