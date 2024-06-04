<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function user(Request $request){

        return User::all();
        
    }
    // REGISTER 
    public function register(Request $request){
        // $request->validate([
        //     'firstname' => 'required|string|max:20',
        //     'lastname' => 'required|string|max:20',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => 'required|string|min:8',
        // ]);
        
        // $user = User::create([
        //     'firstname' => $request->input('firstname'),
        //     'lastname' => $request->input('lastname'),
        //     'email' => $request->input('email'),
        //     'password' => Hash::make($request->input('password'))
        // ]);
        
        
        // // Return a response
        // return response()->json([
        //     'message' => 'User registered successfully!',
        //     'user' => $user
        // ], 201);
        // Validate the incoming request
        $request->validate([
            'firstname' => 'required|string|max:20',
            'lastname' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        try {
            // Create a new user
            $user = User::create([
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password'))
            ]);

            if ($user) {
                // Return success response
                return response()->json([
                    'message' => 'User registered successfully!',
                    'user' => $user
                ], 201);
            } else {
                // Return bad request response if user creation fails
                return response()->json([
                    'message' => 'User registration failed. Please try again.'
                ], 400);
            }
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return response()->json([
                'message' => 'An error occurred during registration: ' . $e->getMessage()
            ], 500);
        }

    }


    // LOGIN 
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        $user = User::where('email', $request->email)->first();
        // return $user;
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        // dd("hhhhh");

        // generate token 
        $token = $user->createToken('API Token')->plainTextToken;



        $cookie = cookie('auth_token', $token, 60 * 24);

        return response([
            'message'=>$token
        ])->withCookie($cookie);
    }


    // LOGOUT
    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        $cookie = Cookie::forget('auth_token');

        return response()->json(['message' => 'Logout successful'])->withCookie($cookie);
    }
}


