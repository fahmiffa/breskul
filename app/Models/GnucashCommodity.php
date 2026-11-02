<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GnucashCommodity extends Model
{
    use HasFactory;

    protected $table = 'gnucash_commodities';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'namespace',
        'mnemonic',
        'fullname',
        'fraction',
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

    public function accounts()
    {
        return $this->hasMany(GnucashAccount::class, 'commodity_id', 'id');
    }
}
