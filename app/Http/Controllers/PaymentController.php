<?php
namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Payment::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Master Pembayaran";
        return view('master.payment.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form Pembayaran";
        return view('master.payment.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            "nominal" => "required",
        ], [
            'required' => 'Field Wajib disi',
        ]);

        $item          = new Payment;
        $item->name    = $request->name;
        $item->nominal = $request->nominal;
        $item->app     = auth()->user()->app->id;
        $item->save();

        return redirect()->route('dashboard.master.pembayaran.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $pembayaran)
    {
        $action = "Edit";
        $title  = "Form Pembayaran";
        $items  = $pembayaran;
        return view('master.payment.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $pembayaran)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            "nominal" => "required",
        ], [
            'required' => 'Field Wajib disi',
        ]);

        $item          = $pembayaran;
        $item->name    = $request->name;
        $item->nominal = $request->nominal;
        $item->save();

        return redirect()->route('dashboard.master.pembayaran.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $pembayaran)
    {
        Bill::where('payment_id',$pembayaran->id)->delete();
        $pembayaran->delete();
        return back()->with('success', 'Data berhasil di hapus');
    }
}
