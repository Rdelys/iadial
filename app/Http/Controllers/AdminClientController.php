<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::query()
            ->whereNotNull('plan')
            ->when($request->search, function ($q, $s) {
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('company_name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function edit(User $user)
    {
        $payments = Payment::where('user_id', $user->id)->latest()->take(10)->get();
        $appointmentsCount = $user->appointments()->count();

        return view('admin.clients.edit', compact('user', 'payments', 'appointmentsCount'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'vapi_public_key'   => 'nullable|string|max:255',
            'vapi_assistant_id' => 'nullable|string|max:255',
        ]);

        $user->update($data);

        return redirect()->route('admin.clients.edit', $user)
            ->with('success', "Assistant Vapi mis à jour pour " . ($user->company_name ?? $user->name) . '.');
    }
}