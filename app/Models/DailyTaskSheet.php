<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTaskSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'employee_id',
        'assigned_by',
        'date',
        'position',
        'tasks',
        'notes',
        'status',
        'whatsapp_sent_at',
        'whatsapp_status',
        'email_sent_at',
        'email_status',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'tasks' => 'array',
            'whatsapp_sent_at' => 'datetime',
            'email_sent_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Génère une référence séquentielle unique pour la fiche de tâches du jour.
     */
    public static function generateReference(): string
    {
        $prefix = 'TSK-'.date('Ymd').'-';
        $latest = static::where('reference', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $num = (int) substr($latest->reference, -3) + 1;
        } else {
            $num = 1;
        }

        return $prefix.str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Message WhatsApp préformaté pour l'employé.
     */
    public function getWhatsAppMessageAttribute(): string
    {
        $empName = $this->employee ? $this->employee->full_name : 'Collaborateur';
        $dateStr = $this->date ? $this->date->format('d/m/Y') : date('d/m/Y');

        $msg = "📋 *IVOSPHERE ERP — Fiche de Tâches du Jour*\n";
        $msg .= "👤 *Employé(e) :* {$empName}\n";
        $msg .= "💼 *Poste :* {$this->position}\n";
        $msg .= "📅 *Date :* {$dateStr}\n";
        $msg .= "🔖 *Réf :* {$this->reference}\n\n";
        $msg .= "🎯 *Vos objectifs & tâches à réaliser :*\n";

        if (is_array($this->tasks)) {
            foreach ($this->tasks as $index => $task) {
                $num = $index + 1;
                $taskTitle = is_array($task) ? ($task['title'] ?? '') : (string) $task;
                $msg .= "{$num}. [ ] {$taskTitle}\n";
            }
        }

        if ($this->notes) {
            $msg .= "\n💡 *Consignes particulières :* {$this->notes}\n";
        }

        $msg .= "\n📄 *Document PDF officiel :* ".route('rh.daily-tasks.print', $this);
        $msg .= "\n_Bonne journée de travail avec l'équipe IVOSPHERE !_";

        return $msg;
    }

    /**
     * Lien direct WhatsApp (Click to chat) vers le numéro de l'employé.
     */
    public function getWhatsAppUrlAttribute(): ?string
    {
        if (! $this->employee || ! $this->employee->phone) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $this->employee->phone);
        if (! $phone) {
            return null;
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->whats_app_message);
    }
}
