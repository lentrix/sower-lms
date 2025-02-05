<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index() {
        return inertia('Users/Index', [
            'users' => User::orderBy('full_Name')->get()->map(function($u) {
                return [
                    'id' => $u->id,
                    'full_name' => $u->full_name,
                    'user_name' => $u->user_name,
                    'email' => $u->email,
                    'permissions' => $u->getAllPermissions()->pluck('name')
                ];
            }),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'full_name' => 'string|required',
            'user_name' => 'string|required',
            'email' => 'string|required',
            'password' => 'string|required|confirmed'
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->syncPermissions($request->permissions);

        return back()->with('success','The new user has been created');
    }

    public function update(User $user, Request $request) {
        $request->validate([
            'full_name' => 'string|required',
            'user_name' => 'string|required',
            'email' => 'string|required',
        ]);

        $user->update($request->only('full_name','user_name','email'));

        if($request->password) {
            $user->update(['password'=>bcrypt($request->password)]);
        }

        $user->syncPermissions($request->permissions);

        return back()->with('success', 'User has been updated.');
    }

    public function destroy(User $user) {
        $name = $user->full_name;
        $user->delete();
        return back()->with('success','User ' . $name . ' has been deleted.');
    }
}
