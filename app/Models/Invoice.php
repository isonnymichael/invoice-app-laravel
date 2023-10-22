<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'total',
        'customer_id',
        'sub_total',
        'date',
        'discount',
        'due_date',
        'reference',
        'number',
        'terms_and_conditions'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class,"customer_id","id");
    }

    public function invoice_items()
    {
        return $this->hasMany(InvoiceItem::class,"invoice_id","id");
    }
}
