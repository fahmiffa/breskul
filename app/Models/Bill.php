<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{

    protected $appends = ['state'];

    public function getstateAttribute()
    {
        if ($this->status == 0) {
            return "Tagihan";
        }
    }

    public function head()
    {
        return $this->belongsTo(Head::class, 'head_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }
}
