<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\AppSetting;
use App\Models\Bill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:send-reminders {--days-before=3 : Days before due date to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminder emails to consumers with upcoming due dates or overdue bills';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting payment reminder email process...');

        $daysBefore = (int) $this->option('days-before');
        $today = now()->startOfDay();
        $reminderDate = $today->copy()->addDays($daysBefore);

        $upcomingCount = 0;
        $penaltyDayCount = 0;

        // 1. Send reminders for bills with due date approaching (X days before)
        $upcomingBills = Bill::with('consumer.user')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('balance', '>', 0)
            ->whereDate('due_date_end', '=', $reminderDate->toDateString())
            ->get();

        foreach ($upcomingBills as $bill) {
            if ($this->sendReminder($bill, 'upcoming')) {
                $upcomingCount++;
            }
        }

        $this->info("Sent {$upcomingCount} upcoming payment reminders ({$daysBefore} days before due).");

        // 2. Send penalty day reminders (due date has passed - penalty applied today)
        // This targets bills where due_date_end was yesterday (penalty kicks in today)
        $penaltyBills = Bill::with('consumer.user')
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->where('balance', '>', 0)
            ->whereDate('due_date_end', '=', $today->copy()->subDay()->toDateString())
            ->get();

        foreach ($penaltyBills as $bill) {
            // Apply penalty if not already applied
            if ($bill->penalty <= 0) {
                $penaltyFee = (float) AppSetting::getValue('penalty_fee', 50);
                $bill->penalty = $penaltyFee;
                $bill->total_amount += $penaltyFee;
                $bill->balance = $bill->total_amount - $bill->amount_paid;
                $bill->status = 'overdue';
                $bill->save();
            }

            if ($this->sendReminder($bill, 'penalty_day')) {
                $penaltyDayCount++;
            }
        }

        $this->info("Sent {$penaltyDayCount} penalty day reminders.");

        $totalSent = $upcomingCount + $penaltyDayCount;
        $this->info("Total emails sent: {$totalSent}");

        return Command::SUCCESS;
    }

    /**
     * Send reminder email to a consumer.
     */
    private function sendReminder(Bill $bill, string $type): bool
    {
        $email = $bill->consumer->user->email ?? null;

        if (empty($email)) {
            $this->warn("Bill #{$bill->id}: No email address for consumer.");

            return false;
        }

        try {
            Mail::to($email)->send(new PaymentReminderMail($bill, $type));
            $this->line("Bill #{$bill->id}: Sent {$type} reminder to {$email}");

            return true;
        } catch (\Exception $e) {
            $this->error("Bill #{$bill->id}: Failed to send to {$email} - ".$e->getMessage());
            \Log::error("Payment reminder failed for bill #{$bill->id}: ".$e->getMessage());

            return false;
        }
    }
}
