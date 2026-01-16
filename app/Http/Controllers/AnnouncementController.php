<?php

namespace App\Http\Controllers;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\Consumer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request): View
    {
        $query = Announcement::with('createdBy')->latest();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active()->current();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('ends_at', '<', now()->toDateString());
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $announcements = $query->paginate(15);

        return view('announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        return view('announcements.create');
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'type' => 'required|in:info,warning,urgent',
            'target_audience' => 'required|in:all,consumers,staff',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'send_email' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['send_email'] = $request->boolean('send_email');
        $validated['is_active'] = true;

        $announcement = Announcement::create($validated);

        // Queue email notifications to consumers if enabled
        $message = 'Announcement created successfully.';

        if ($request->boolean('send_email')) {
            $emailCount = $this->queueAnnouncementEmails($announcement);
            if ($emailCount > 0) {
                $message .= " Queued {$emailCount} email(s) for sending.";
            }
        }

        return redirect()
            ->route('announcements.index')
            ->with('success', $message);
    }

    /**
     * Display the specified announcement.
     */
    public function show(Announcement $announcement): View
    {
        return view('announcements.show', [
            'announcement' => $announcement->load('createdBy'),
        ]);
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(Announcement $announcement): View
    {
        return view('announcements.edit', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'type' => 'required|in:info,warning,urgent',
            'target_audience' => 'required|in:all,consumers,staff',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $announcement->update($validated);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    /**
     * Toggle announcement active status.
     */
    public function toggle(Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'is_active' => ! $announcement->is_active,
        ]);

        $status = $announcement->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Announcement {$status} successfully.");
    }

    /**
     * Queue announcement emails to consumers using Laravel's queue.
     */
    private function queueAnnouncementEmails(Announcement $announcement): int
    {
        // Get consumers with valid emails
        $consumers = Consumer::with('user')
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email')->where('email', '!=', '');
            })
            ->get();

        if ($consumers->isEmpty()) {
            return 0;
        }

        // Queue each email using Laravel's built-in queue
        foreach ($consumers as $consumer) {
            Mail::to($consumer->user->email)->queue(new AnnouncementMail($announcement));
        }

        return $consumers->count();
    }
}
