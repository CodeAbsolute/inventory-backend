<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /* Creates a new User */
    public function addUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required | min:10 | max:100',
                'email' => 'email | required | unique:users',
                'phone' => 'required | integer | min:1111111111 | max:9999999999 | unique:users',
                'password' => ' min:6 | max:12'
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 422);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make('12345678');
            // send email for verification
            try {

                $token = Str::random(60);
                $data['email'] = $user->email;
                $data['subject'] = 'Verify Email Notification';
                $data['from'] = 'mahesh.gajakosh@peerconnexions.com';
                $data['url'] = "http://localhost:3000/verifyEmail/" . $token;
                $user->remember_token = $token;
                Mail::send('mail.verify-email', ['data' => $data], function ($message) use ($data) {
                    $message
                        ->to($data['email'])
                        ->subject($data['subject'])
                        ->from($data['from']);
                });
                $user->save();
                // storing password reset token
                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);

                return response(['success' => true, 'messages' => ['Verification Email sent successfully', 'User Added Successfully'], 'user' => $user], 201);
            } catch (Exception $e) {
                return response(['error' => $e->getMessage()], 500);
            }

        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }

    /* Returns the logged in user data */
    public function user(Request $request)
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

    /* Returns the data of a single user */
    public function singleUser($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                return response(['message' => 'success', 'user' => $user], 200);
            } else {
                return response(['message' => 'User does not exist'], 404);
            }
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 401);
        }
    }

    /* Returns the data of all users */
    public function allUsers()
    {
        try {
            $users = User::all();
            return response(['message' => 'success', 'count' => count($users), 'users' => $users]);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 503);
        }
    }

    /* Deletes a single user */
    public function deleteUser($id)
    {
        try {
            $user = User::find($id);
            if (auth()->user()->id == $id) {
                return response(['message' => 'You cannot delete yourself'], 401);
            }
            if ($user) {
                $user->delete();
                return response(['message' => 'User deleted successfully'], 200);
            } else {
                return response(['message' => 'User does not exist'], 404);
            }
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 401);
        }
    }

}