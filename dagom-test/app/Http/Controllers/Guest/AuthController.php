<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Managers\Guest\AuthManager;
use App\Models\User;
use App\Notifications\EmailVerfication;
use App\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $manager;

    public function __construct(AuthManager $manager)
    {
        $this->manager = $manager;
    }

    public function login(Request $request)
    {
        $response = $this->manager->login($request);
        return response()->json($response);
    }

    public function register(Request $request)
    {
        $response = $this->manager->register($request);
        return response()->json($response);
    }

    public function Unauthorized()
    {
        return response()->json('Unauthorized',401);
    }

    /**
     * Logout the specified access token from storage.
     *
     * @param  int  $user
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $response = [];
        try {
            Auth::user()->currentAccessToken()->delete();
            $response["message"] = "Logout Successfully";
            $response["error"] = false;
        } catch (\Exception $error) {
            $response["message"] = "Error ".$error->getMessage();
            $response["error"] = true;
        }

        return response()->json($response);
    }

    /**
     * Send Email Password Verification to
     * the specified access token from storage.
     *
     * @param  int  $user
     * @return \Illuminate\Http\Response
     */

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $response = [];
        try {
            $user = User::where('email',$request->email)->first();
            if(!$user){
                $response["message"] = "Email is not recognize!";
                $response["error"] = true;
            }elseif($user->email_verified_at == null){
                $response["message"] = "Your Email is not verified yet";
                $response["error"] = true;
                $response["need"] = "Need Verification";
            }else{
                $this->code =(string) random_int(1000,90000);
                $user->notify(new ResetPassword($this->code, $user));
                if($user->verificationCode == null){
                    $user->verificationCode()->create([
                        'code' => $this->code
                    ]);
                }else{
                    $user->verificationCode()->update([
                        'code' => $this->code
                    ]);
                }
                $response["message"] = "We sent reset password link in your Email";
                $response["error"] = false;
            }
        } catch (\Exception $error) {
            $response["message"] = "Error ".$error->getMessage();
            $response["error"] = true;
        }

        return response()->json($response);
    }


    public function verificationCodeCheck(Request $request, User $user)
    {
        $response = [];
        $code = $user->verificationCode->code;
        if($request->code == $code){
            $response["message"] = "Authorized!";
            $response["error"] = false;
            $user->verificationCode()->delete();
        }else{
            $response["message"] = "Code is mismatch!";
            $response["error"] = true;
        }
        return response()->json($response);
    }

    public function resetPassword(Request $request, User $user)
    {
        $validation = Validator::make($request->all(),[
            'new_password' => 'required|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'confirm_password' => 'required'
        ]);

        $response = [];

        if($validation->fails()){
            $response["message"] = $validation->errors();
            $response["error"] = true;
        }else{
            $user->update($request->all());
            $response["message"] = "Successfully Reseting Your Password";
            $response["error"] = false;
        }

        return response()->json($response);
    }

}
