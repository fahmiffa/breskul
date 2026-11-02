<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GnucashSplit extends Model
{
    use HasFactory;

    protected $table = 'gnucash_splits';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'transaction_id',
        'account_id',
        'memo',
        'action',
        'reconcile_state',
        'reconcile_date',
        'value',
        'quantity',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'quantity' => 'decimal:2',
        'reconcile_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = str_replace('-', '', (string) Str::uuid());
            }
        });
    }

    public function transaction()
    {
        return $this->belongsTo(GnucashTransaction::class, 'transaction_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(GnucashAccount::class, 'account_id', 'id');
    }

    public function getDebitAttribute(): float
    {
        return $this->value > 0 ? (float) $this->value : 0.0;
    }

    public function getCreditAttribute(): float
    {
        return $this->value < 0 ? abs((float) $this->value) : 0.0;
    }

    /**
     * Get Tambah (Increase) amount based on account normal balance
     */
    public function getTambah(string $normalBalance = 'DEBIT'): float
    {
        $val = (float) $this->value;
        if ($normalBalance === 'CREDIT') {
            return $val < 0 ? abs($val) : 0.0;
        }
        return $val > 0 ? $val : 0.0;
    }

    /**
     * Get Kurang (Decrease) amount based on account normal balance
     */
    public function getKurang(string $normalBalance = 'DEBIT'): float
    {
        $val = (float) $this->value;
        if ($normalBalance === 'CREDIT') {
            return $val > 0 ? $val : 0.0;
        }
        return $val < 0 ? abs($val) : 0.0;
    }

    public function getFormattedDebitAttribute(): string
    {
        return $this->debit > 0 ? 'Rp ' . number_format($this->debit, 2, ',', '.') : '-';
    }

    public function getFormattedCreditAttribute(): string
    {
        return $this->credit > 0 ? 'Rp ' . number_format($this->credit, 2, ',', '.') : '-';
    }
}
