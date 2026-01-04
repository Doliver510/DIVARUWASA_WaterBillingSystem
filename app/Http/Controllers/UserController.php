<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of staff users (non-consumers).
     */
    public function index()
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $users = User::with('role')
            ->whereHas('role', fn ($q) => $q->where('slug', '!=', 'consumer'))
            ->latest()
            ->paginate(15);

        $roles = Role::where('slug', '!=', 'consumer')->get();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Store a newly created staff user.
     */
    public function store(Request $request)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        // Ensure role is not consumer
        $role = Role::find($validated['role_id']);
        if ($role->slug === 'consumer') {
            return back()->withErrors(['role_id' => 'Cannot assign consumer role via this form.']);
        }

        // Generate default password: {LastName}@divaruwasa
        $defaultPassword = $this->generateDefaultPassword($validated['last_name']);

        $validated['password'] = Hash::make($defaultPassword);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully. Default password: '.$defaultPassword);
    }

    /**
     * Generate default password from last name.
     */
    private function generateDefaultPassword(string $lastName): string
    {
        // Clean last name: remove non-alpha chars and capitalize first letter
        $cleanedLastName = ucfirst(preg_replace('/[^a-zA-Z]/', '', $lastName));

        return $cleanedLastName.'@divaruwasa';
    }

    /**
     * Update the specified staff user.
     */
    public function update(Request $request, User $user)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        if ($user->role->slug === 'consumer') {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'reset_password' => ['nullable', 'boolean'],
        ]);

        // Ensure role is not consumer
        $role = Role::find($validated['role_id']);
        if ($role->slug === 'consumer') {
            return back()->withErrors(['role_id' => 'Cannot assign consumer role via this form.']);
        }

        $message = 'User updated successfully.';

        // Reset password if requested
        if ($request->boolean('reset_password')) {
            $defaultPassword = $this->generateDefaultPassword($validated['last_name']);
            $validated['password'] = Hash::make($defaultPassword);
            $message = 'User updated successfully. New password: '.$defaultPassword;
        }

        unset($validated['reset_password']);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', $message);
    }

    /**
     * Remove the specified staff user.
     */
    public function destroy(User $user)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        // Don't allow deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Don't allow deleting consumers via this controller
        if ($user->role->slug === 'consumer') {
            abort(404);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
