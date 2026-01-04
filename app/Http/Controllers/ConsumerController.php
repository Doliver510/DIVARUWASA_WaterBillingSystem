<?php

namespace App\Http\Controllers;

use App\Models\Consumer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ConsumerController extends Controller
{
    /**
     * Display a listing of consumers.
     */
    public function index()
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $consumers = Consumer::with('user')->latest()->paginate(15);

        // Generate next ID for the form
        $nextIdNo = Consumer::generateNextIdNo();

        return view('consumers.index', compact('consumers', 'nextIdNo'));
    }

    /**
     * Store a newly created consumer.
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
            'id_no' => ['nullable', 'string', 'max:10', 'unique:consumers', 'regex:/^[0-9]+$/'],
            'address' => ['required', 'string', 'max:500'],
            'status' => ['required', 'in:active,disconnected'],
        ]);

        // Auto-generate ID if not provided
        $idNo = $validated['id_no'] ?? Consumer::generateNextIdNo();

        // Ensure ID is zero-padded to at least 3 digits
        $idNo = str_pad($idNo, 3, '0', STR_PAD_LEFT);

        // Generate default password: {LastName}@{IDNo}
        $defaultPassword = $this->generateDefaultPassword($validated['last_name'], $idNo);

        DB::transaction(function () use ($validated, $idNo, $defaultPassword) {
            $consumerRole = Role::where('slug', 'consumer')->first();

            $user = User::create([
                'role_id' => $consumerRole->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($defaultPassword),
            ]);

            Consumer::create([
                'user_id' => $user->id,
                'id_no' => $idNo,
                'address' => $validated['address'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('consumers.index')->with('success', 'Consumer created successfully. Default password: '.$defaultPassword);
    }

    /**
     * Generate default password from last name and ID number.
     */
    private function generateDefaultPassword(string $lastName, string $idNo): string
    {
        // Clean last name: remove non-alpha chars and capitalize first letter
        $cleanedLastName = ucfirst(preg_replace('/[^a-zA-Z]/', '', $lastName));

        return $cleanedLastName.'@'.$idNo;
    }

    /**
     * Display the specified consumer.
     */
    public function show(Consumer $consumer)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $consumer->load('user');

        return view('consumers.show', compact('consumer'));
    }

    /**
     * Update the specified consumer.
     */
    public function update(Request $request, Consumer $consumer)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$consumer->user_id],
            'id_no' => ['required', 'string', 'max:10', 'unique:consumers,id_no,'.$consumer->id, 'regex:/^[0-9]+$/'],
            'address' => ['required', 'string', 'max:500'],
            'status' => ['required', 'in:active,disconnected'],
            'reset_password' => ['nullable', 'boolean'],
        ]);

        // Ensure ID is zero-padded to at least 3 digits
        $idNo = str_pad($validated['id_no'], 3, '0', STR_PAD_LEFT);

        $message = 'Consumer updated successfully.';
        $newPassword = null;

        // Reset password if requested
        if ($request->boolean('reset_password')) {
            $newPassword = $this->generateDefaultPassword($validated['last_name'], $idNo);
            $message = 'Consumer updated successfully. New password: '.$newPassword;
        }

        DB::transaction(function () use ($validated, $consumer, $idNo, $newPassword) {
            $userData = [
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
            ];

            if ($newPassword) {
                $userData['password'] = Hash::make($newPassword);
            }

            $consumer->user->update($userData);

            $consumer->update([
                'id_no' => $idNo,
                'address' => $validated['address'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('consumers.index')->with('success', $message);
    }

    /**
     * Remove the specified consumer.
     */
    public function destroy(Consumer $consumer)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        DB::transaction(function () use ($consumer) {
            $user = $consumer->user;
            $consumer->delete();
            $user->delete();
        });

        return redirect()->route('consumers.index')->with('success', 'Consumer deleted successfully.');
    }
}
