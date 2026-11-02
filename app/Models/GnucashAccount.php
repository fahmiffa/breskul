<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GnucashAccount extends Model
{
    use HasFactory;

    protected $table = 'gnucash_accounts';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'account_type',
        'commodity_id',
        'commodity_scu',
        'code',
        'description',
        'parent_id',
        'placeholder',
        'color',
        'notes',
    ];

    protected $casts = [
        'placeholder' => 'boolean',
        'commodity_scu' => 'integer',
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

    public function parent()
    {
        return $this->belongsTo(GnucashAccount::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(GnucashAccount::class, 'parent_id', 'id')->orderBy('code')->orderBy('name');
    }

    public function splits()
    {
        return $this->hasMany(GnucashSplit::class, 'account_id', 'id');
    }

    public function commodity()
    {
        return $this->belongsTo(GnucashCommodity::class, 'commodity_id', 'id');
    }

    /**
     * Get normal balance type: DEBIT or CREDIT
     */
    public function getNormalBalanceAttribute(): string
    {
        return in_array(strtoupper($this->account_type), ['ASSET', 'BANK', 'CASH', 'EXPENSE']) ? 'DEBIT' : 'CREDIT';
    }

    /**
     * Calculate current balance based on splits
     */
    public function calculateBalance(): float
    {
        $sum = (float) $this->splits()->sum('value');
        if ($this->normal_balance === 'CREDIT') {
            return -$sum;
        }
        return $sum;
    }

    public function getFormattedBalanceAttribute(): string
    {
        $bal = $this->calculateBalance();
        return ($bal < 0 ? '-Rp ' : 'Rp ') . number_format(abs($bal), 2, ',', '.');
    }

    /**
     * Get full path name with colon separator (e.g. Assets:Asset Lancar:Kas Ikhwan Pebatan)
     */
    public function getFullPathAttribute(): string
    {
        $names = [$this->name];
        $curr = $this->parent;
        while ($curr && $curr->account_type !== 'ROOT') {
            $names[] = $curr->name;
            $curr = $curr->parent;
        }
        return implode(':', array_reverse($names));
    }

    /**
     * Get full path name with spaces (e.g. Assets : Asset Lancar : BSI)
     */
    public function getFullNameAttribute(): string
    {
        $names = [$this->name];
        $curr = $this->parent;
        while ($curr && $curr->account_type !== 'ROOT') {
            $names[] = $curr->name;
            $curr = $curr->parent;
        }
        return implode(' : ', array_reverse($names));
    }

    /**
     * Check if account has no children (leaf account)
     */
    public function getIsLeafAttribute(): bool
    {
        return $this->children()->count() === 0;
    }
}
