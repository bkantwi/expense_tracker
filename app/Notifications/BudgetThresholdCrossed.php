<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BudgetThresholdCrossed extends Notification
{
    use Queueable;

    public function __construct(
        public Budget $budget,
        public float $spent,
        public float $percent, // e.g. 87.5
        public string $level   // 'warn'|'at'|'over'
    ) {}

    public function via($notifiable): array
    {
        // Database is always useful; add 'mail' if you have mail configured
        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $month = $this->budget->period?->format('M Y') ?? '';
        $cat   = $this->budget->category->name ?? 'Budget';

        $subject = match ($this->level) {
            'over' => "Over budget: $cat ($month)",
            'at'   => "Reached budget: $cat ($month)",
            default=> "Warning: {$this->percent}% of $cat budget ($month)",
        };

        return (new MailMessage)
            ->subject($subject)
            ->line("Category: {$cat}")
            ->line("Period: {$month}")
            ->line("Budget: ₵".number_format($this->budget->amount,2))
            ->line("Spent: ₵".number_format($this->spent,2)." ({$this->percent}%)");
    }

    public function toDatabase($notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'category'  => $this->budget->category->name ?? null,
            'period'    => $this->budget->period?->format('Y-m-d'),
            'amount'    => (float) $this->budget->amount,
            'spent'     => $this->spent,
            'percent'   => $this->percent,
            'level'     => $this->level, // 'warn'|'at'|'over'
        ];
    }
}
