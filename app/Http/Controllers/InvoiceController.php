<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function get_all_invoice()
    {
       
        $invoices = Invoice::with(['customer' => function ($query) {
            $query->select('id','firstname');
        }])->orderBy('id', 'DESC')->get();
       
        return response()->json([
            'invoices' => $invoices
        ],200);
    }

    public function search_invoice(Request $request)
    {
        DB::enableQueryLog();
        $search = $request->get("s");
        if($search != null){
            $invoices = Invoice::with('customer')->whereHas('customer',function($q) use ($search){
                $q->where('firstname', 'LIKE', '%'. $search .'%');
             })
            ->orWhere('id','LIKE', '%'. $search .'%')
            ->get();

            return response()->json([
                'invoices' => $invoices
            ],200);
        }else{
            return $this->get_all_invoice();
        }

    }
}
