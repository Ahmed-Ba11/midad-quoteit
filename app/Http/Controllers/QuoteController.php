<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotes = Quote::latest()->limit(20)->get();
        return view('welcome', compact('quotes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuoteRequest $request)
    {
        Auth::user()->quotes()->create($request->validated());
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuoteRequest $request, Quote $quote)
    {
        abort_if($quote->user_id != Auth::id(), 403);

        $quote->update($request->validated());
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        abort_if($quote->user_id != Auth::id(), 403);
        $quote->delete();
        return redirect()->back();
    }
}
