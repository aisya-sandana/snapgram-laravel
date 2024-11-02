<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller {
     Public function index() {
          $user = Auth::user();
          return view('profile', compact('user'));
     }

     // update user profile
     public function update(Request $request) {
          $user = Auth::user();
          $request->validate([
               'username' => 'required|string|max:255',
               'namaLengkap' => 'required|string|max:255',
               'email' => 'required|email|max:255',
               'password' => 'nullable|string|min:8|confirmed',
          ]);

          $user->username = $request->username;
          $user->namaLengkap = $request->namaLengkap;
          $user->email = $request->email;

          if ($request->filled('password')) {
               $user->password= Hash::make($request->password);
          }

          $user->save();
          return redirect()->route('profile.index');
     }
}