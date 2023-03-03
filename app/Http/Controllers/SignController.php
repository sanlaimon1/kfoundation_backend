<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // not finished
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // not finished
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // no need to show
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // disable to edit
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // disable to update
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
