<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

    public function create_invoice(Request $request)
    {
        $counter = Counter::where('key','invoice')->first();
        $random = Counter::where('key','invoice')->first();

        $invoice = Invoice::orderBy('id','DESC')->first();
        if($invoice){
            $invoice = $invoice->id + 1;
            $counters = $counter->value + $invoice;
        }else{
            $counters = $counter->value;
        }

        $formData = [
            'number' => $counter->prefix.$counters,
            'customer_id' => null,
            'customer' => null,
            'date' => date('Y-m-d'),
            'due_date' => null,
            'reference' => null,
            'discount' => 0,
            'terms_and_conditions' => 'Default Terms and Condition',
            'items' => [
                [
                    'product_id' => null,
                    'product' => null,
                    'unit_price' => 0,
                    'quantity' => 1
                ]
            ]
        ];

        return response()->json($formData);
    }

    public function add_invoice(Request $request)
    {
        $invoiceitem = $request->input("invoice_item");
        $invoicedata = $request->except(["invoice_item"]);

        $invoice = Invoice::create($invoicedata);
        foreach (json_decode($invoiceitem) as $item) {
           $itemdata['product_id'] = $item->id;
           $itemdata['invoice_id'] = $invoice->id;
           $itemdata['quantity'] = $item->quantity;
           $itemdata['unit_price'] = $item->unit_price;

           InvoiceItem::create($itemdata);
        }
        
    }
    public function show_invoice($id)
    {
        $invoice = Invoice::with(['customer','invoice_items.product'])->find($id);
        return response()->json([
            'invoice' => $invoice
        ],200);
    }

    public function edit_invoice($id)
    {
        $invoice = Invoice::with(['customer','invoice_items.product'])->find($id);
        return response()->json([
            'invoice' => $invoice
        ],200);
    }

    public function delete_invoice_items($id)
    {
        $invoiceItem = InvoiceItem::findOrFail($id);
        $invoiceItem->delete();
    }

    public function update_invoice(Request $request, $id)
    {
        $invoicedata = $request->except(["invoice_item"]);
        $invoiceitem = $request->input("invoice_item");

        $invoice = Invoice::where('id',$id)->first();
        $invoice->update($invoicedata);
        $invoice->invoice_items()->delete();
        foreach (json_decode($invoiceitem) as $item) {
            $itemdata['product_id'] = $item->product_id;
            $itemdata['invoice_id'] = $id;
            $itemdata['quantity'] = $item->quantity;
            $itemdata['unit_price'] = $item->unit_price;
 
            InvoiceItem::create($itemdata);
         }
    }

    public function delete_invoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->invoice_items()->delete();
        $invoice->delete();
    }
}
