<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function index()
    {
        $keys = ApiKey::latest()->get();
        return view('admin.api-keys.index', compact('keys'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        ['record' => $record, 'plain_key' => $plainKey] = ApiKey::generate($request->name);

        return redirect()->route('admin.api-keys.index')
            ->with('new_key',      $plainKey)
            ->with('new_key_name', $record->name);
    }

    public function revoke(ApiKey $apiKey)
    {
        $apiKey->update(['is_active' => false]);

        return redirect()->route('admin.api-keys.index')
            ->with('success', "API key \"{$apiKey->name}\" has been revoked.");
    }

    public function destroy(ApiKey $apiKey)
    {
        $apiKey->delete();

        return redirect()->route('admin.api-keys.index')
            ->with('success', "API key \"{$apiKey->name}\" deleted.");
    }
}
