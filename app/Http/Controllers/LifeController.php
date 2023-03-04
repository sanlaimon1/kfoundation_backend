<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Life;

class LifeController extends Controller
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
        $lifes = Life::orderBy('sort','asc')->paginate(10);
        return view( 'life.index', compact('lifes') );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('life.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'production_name'=> ['required', 'string', 'between:1,40'],
            'sort'=> ['required', 'integer', 'gt:0'],
            'picture'=> ['required','image','mimes:jpg,png,jpeg,bmp,webp'],
        ]);

        $production_name = trim( $request->get('production_name') );
        $sort = trim( $request->get('sort') );
        $extra = trim( $request->get('extra') );
        $inputs = trim( $request->get('inputs') );

        $sort = (int)$sort;

        if($request->hasFile('picture')){
            $picture = time().'.'.$request->picture->extension();
            $request->picture->move(public_path('/images/'),$picture);
            $image = '/images/'.$picture;
        }

        $newlife = new Life();
        $newlife->production_name = $production_name;
        $newlife->picture = $image;
        $newlife->sort = $sort;
        $newlife->extra = $extra;
        $newlife->inputs = $inputs;
        $newlife->save();

        return redirect()->route('life.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $life = Life::find($id);
        return view('life.edit', compact('life'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'production_name'=> ['required', 'string', 'between:1,40'],
            'sort'=> ['required', 'integer', 'gt:0'],
            'picture'=> ['image','mimes:jpg,png,jpeg,bmp,webp'],
        ]);

        $production_name = trim( $request->get('production_name') );
        $sort = trim( $request->get('sort') );
        $extra = trim( $request->get('extra') );
        $inputs = trim( $request->get('inputs') );

        $sort = (int)$sort;

        if($request->hasFile('picture')){
            $picture = time().'.'.$request->picture->extension();
            $request->picture->move(public_path('/images/'),$picture);
            $image = '/images/'.$picture;
        } else {
            $image = $request->old_picture;
        }

        $newlife = Life::find($id);
        $newlife->production_name = $production_name;
        $newlife->picture = $image;
        $newlife->sort = $sort;
        $newlife->extra = $extra;
        $newlife->inputs = $inputs;
        $newlife->save();

        return redirect()->route('life.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $life = Life::find($id);
        $life->delete();
        return redirect()->route('life.index');
    }
}
