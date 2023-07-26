<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected function sendEmail($filename, $data)
    {
        Mail::send($filename, ['data' => $data], function ($message) use ($data) {
            $message
                ->to($data['email'])
                ->subject($data['title'])
                ->from('mahesh.gajakosh@peerconnexions.com');
        });

    }
    // Verify Email
    public function verifyMail($token)
    {
        try {
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                return response(['message' => 'Invalid token!'], 422);
            }
            $user->email_verified_at = Carbon::now();
            $user->remember_token = null;
            $user->save();
            return response(['success' => true, 'message' => 'Email verified successfully!'], 200);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }


    /* Reset Password */
    public function resetPassword(Request $request, $token)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required | min:6 | max:12',
                'confirmPassword' => 'required | min:6 | max:12 | same:password'
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 422);
            }

            $passwordReset = DB::table('password_resets')->where([
                'token' => $token
            ])->first();

            if (!$passwordReset) {
                return response(['error' => 'Invalid token!'], 422);
            }

            $user = User::where('email', $passwordReset->email)->first();
            if (!$user) {
                return response(['error' => 'User does not exist!'], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_resets')->where([
                ['email', $passwordReset->email]
            ])->delete();
            return response(['success' => true, 'message' => 'Password changed successfully!'], 200);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }

    /* Forgot Password */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'email|required'
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response(['message' => 'User not found'], 404);
            }
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => Str::random(60),
                'created_at' => Carbon::now()
            ]);

            $tokenData = DB::table('password_resets')->where('email', $request->email)->first();
            $data = [
                'email' => $request->email,
                'title' => 'Reset Password Notification',
                'url' => 'http://localhost:3000/resetPassword/' . $tokenData->token
            ];
            sendEmail('mail.reset-password', $data);

            return response(['success' => true, 'message' => 'Reset password link sent on your email id.']);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }



    /* Logins the user inside the application */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'email|required',
                'password' => 'required | min:6 | max:12'
            ]);

            if ($validator->fails()) {
                // echo $validator->messages();
                return response(['errors' => $validator->errors()], 422);
            }
            // print_r($validator->validated());
            if (!auth()->attempt($validator->validated())) {
                return response(['message' => 'Invalid Credentials'], 401);
            }
            if (auth()->user()->email_verified_at == null) {
                return response(['message' => 'Please verify your email before login'], 401);
            }

            $accessToken = auth()->user()->createToken('authToken')->accessToken;

            return response(['user' => auth()->user(), 'token' => $accessToken], 200);

        } catch (Exception $e) {
            return response(['error' => $e->getMessage(), 'message' => 'Invalid Credentials'], 500);
        }
    }

    /* Returns the logged in user data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLoggedInUserData(Request $request)
    {
        try {
            if ($request->user()) {
                return response(['user' => $request->user()]);
            } else {
                return response(['message' => 'User Not Found'], 404);
            }
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }

    /* Logout the logged in user */
    public function logout()
    {
        try {
            if (!auth()->user()) {
                return response(['message' => 'User not found'], 401);
            }
            auth()->user()->token()->revoke();
            return response(['message' => 'Successfully logged out'], 200);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 401);
        }
    }
}