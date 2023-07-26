<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserResourceController extends Controller
{
    /**
     * Display a listing of all the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $users = User::all();
            return response(['message' => 'success', 'count' => count($users), 'users' => $users]);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 503);
        }
    }



    /**
     * Creates a new user and sends a verification email
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required | min:10 | max:100',
                'email' => 'email | required | unique:users',
                'phone' => 'required | integer | min:1000000000 | max:9999999999 | unique:users'
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
                // Mail::send('mail.verify-email', ['data' => $data], function ($message) use ($data) {
                //     $message
                //         ->to($data['email'])
                //         ->subject($data['subject'])
                //         ->from($data['from']);
                // });
                sendEmail('mail.verify-email', $data);
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

    /* 
     * Returns the data of a single user 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                return response(['message' => 'success', 'user' => $user], 200);
            } else {
                return response(['message' => 'User does not exist'], 404);
            }
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }



    /**
     * Delete the specified user from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (auth()->user()->id == $id) {
                return response(['message' => 'You cannot delete yourself'], 403);
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