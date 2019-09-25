<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\User;
use Hash;

class AuthController extends Controller
{
  /**
   * Create a new AuthController instance.
   *
   * @return void
   */
  public function __construct()
  {
      $this->middleware('auth:api', ['except' => ['login','register']]);
  }

  /**
   * Get a JWT via given credentials.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function login(Request $request)
  {
      $request->validate([
          'email'   => 'required',
          'password'=> 'required',
      ]);
      $credentials = request(['email', 'password']);

      if (! $token = auth()->attempt($credentials)) {
          return response()->json(['error' => 'Unauthorized'], 401);
      }

      return $this->respondWithToken($token);
  }

  /**
   * Get the authenticated User.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function me()
  {
      return response()->json(auth()->user());
  }

  /**
   * Update the authenticated User.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(Request $request)
  {
      auth()->user()->update($request->all());
      return response('update', Response::HTTP_ACCEPTED);
  }

  /**
   * Log the user out (Invalidate the token).
   *
   * @return \Illuminate\Http\JsonResponse
   */

  public function logout()
  {
      auth()->logout();

      return response()->json(['message' => 'Successfully logged out']);
  }

  /**
   * Refresh a token.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function refresh()
  {
      return $this->respondWithToken(auth()->refresh());
  }

  /**
   * Get the token array structure.
   *
   * @param  string $token
   *
   * @return \Illuminate\Http\JsonResponse
   */
  protected function respondWithToken($token)
  {
      return response()->json([
          'access_token' => $token,
          'token_type' => 'bearer',
          'expires_in' => auth()->factory()->getTTL() * 60,
          'user' => auth()->user()
      ]);
  }

  public function register()
  {
      User::create([
          'name' => request('name'),
          'email' => request('email'),
          'password' => Hash::make(request('password'))
      ]);

      return $this->login(request());
  }
}
