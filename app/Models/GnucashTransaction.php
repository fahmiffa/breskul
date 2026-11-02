<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GnucashTransaction extends Model
{
    use HasFactory;

    protected $table = 'gnucash_transactions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'currency',
        'num',
        'post_date',
        'enter_date',
        'description',
        'notes',
    ];

    protected $casts = [
        'post_date' => 'datetime',
        'enter_date' => 'datetime',
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

    public function splits()
    {
        return $this->hasMany(GnucashSplit::class, 'transaction_id', 'id');
    }

    /**
     * Total transaction amount (sum of positive splits / total debit)
     */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->splits->where('value', '>', 0)->sum('value');
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 2, ',', '.');
    }

    /**
     * Check if transaction splits are balanced (sum of values == 0)
     */
    public function isBalanced(): bool
    {
        $sum = $this->splits->sum('value');
        return abs((float)$sum) < 0.001;
    }
}
