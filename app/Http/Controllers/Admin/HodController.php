<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HodController extends Controller
{
    public function index()
    {
        $hods = User::where('role', 'hod')
            ->latest()
            ->paginate(15);

        return view('admin.hods.index', compact('hods'));
    }

    public function create()
    {
        return view('admin.hods.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'hod',
        ]);

        return redirect()->route('admin.hods.index')
            ->with('success', 'HOD account created successfully.');
    }

    public function edit(User $hod)
    {
        abort_if($hod->role !== 'hod', 403);
        return view('admin.hods.form', compact('hod'));
    }

    public function update(Request $request, User $hod)
    {
        abort_if($hod->role !== 'hod', 403);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => "required|email|unique:users,email,{$hod->id}",
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $hod->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        if (!empty($data['password'])) {
            $hod->update(['password' => Hash::make($data['password'])]);
        }

        return redirect()->route('admin.hods.index')
            ->with('success', 'HOD updated successfully.');
    }

    public function destroy(User $hod)
    {
        abort_if($hod->role !== 'hod', 403);
        $hod->delete();

        return redirect()->route('admin.hods.index')
            ->with('success', 'HOD account deleted.');
    }
}
