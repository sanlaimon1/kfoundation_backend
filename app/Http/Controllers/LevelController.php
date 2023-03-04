<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;
use Illuminate\Support\Facades\Validator;

class LevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 会员等级
     */
    public function index()
    {
        $levels = Level::orderBy('level_id', 'desc')->paginate(20);

        return view('level.index', compact('levels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('level/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'level_name' => ['required', 'string', 'between:1,45'],
            'accumulative_amount' => ['required','integer','gt:0'],
            'interest' => ['required','numeric','between:0,99.99'],
            'personal_charge' => ['required','numeric','between:0,99.99'],
            'level1_award' => ['required','numeric','between:0,99.99'],
            'level2_award' => ['required','numeric','between:0,99.99'],
            'level3_award' => ['required','numeric','between:0,99.99'],
            'min_coin' => ['required', 'integer', 'gte:0'],
            'max_coin' => ['required', 'integer', 'gte:0'],
        ]);

        $level_name = trim($request->level_name);
        $accumulative_amount = trim($request->accumulative_amount);
        $interest = trim($request->interest);
        $personal_charge = trim($request->personal_charge);
        $level1_award = trim($request->level1_award);
        $level2_award = trim($request->level2_award);
        $level3_award = trim($request->level3_award);
        $min_coin = trim($request->min_coin);
        $max_coin = trim($request->max_coin);

        $newlevel = new Level();
        $newlevel->level_name = $level_name;
        $newlevel->accumulative_amount = $accumulative_amount;
        $newlevel->interest = $interest;
        $newlevel->personal_charge = $personal_charge;
        $newlevel->level1_award = $level1_award;
        $newlevel->level2_award = $level2_award;
        $newlevel->level3_award = $level3_award;
        $newlevel->min_coin = $min_coin;
        $newlevel->max_coin = $max_coin;
        $newlevel->save();

        return redirect()->route('level.index');
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
        $level = Level::find($id);
        return view('level.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'level_name' => ['required', 'string', 'between:1,45'],
            'accumulative_amount' => ['required','integer','gt:0'],
            'interest' => ['required','numeric','between:0,99.99'],
            'personal_charge' => ['required','numeric','between:0,99.99'],
            'level1_award' => ['required','numeric','between:0,99.99'],
            'level2_award' => ['required','numeric','between:0,99.99'],
            'level3_award' => ['required','numeric','between:0,99.99'],
            'min_coin' => ['required', 'integer', 'gte:0'],
            'max_coin' => ['required', 'integer', 'gte:0'],
        ]);

        $level_name = trim($request->level_name);
        $accumulative_amount = trim($request->accumulative_amount);
        $interest = trim($request->interest);
        $personal_charge = trim($request->personal_charge);
        $level1_award = trim($request->level1_award);
        $level2_award = trim($request->level2_award);
        $level3_award = trim($request->level3_award);
        $min_coin = trim($request->min_coin);
        $max_coin = trim($request->max_coin);

        $newlevel = Level::find($id);
        $newlevel->level_name = $level_name;
        $newlevel->accumulative_amount = $accumulative_amount;
        $newlevel->interest = $interest;
        $newlevel->personal_charge = $personal_charge;
        $newlevel->level1_award = $level1_award;
        $newlevel->level2_award = $level2_award;
        $newlevel->level3_award = $level3_award;
        $newlevel->min_coin = $min_coin;
        $newlevel->max_coin = $max_coin;
        $newlevel->save();

        return redirect()->route('level.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $level = Level::find($id);
        $level->delete();
        return redirect()->route('level.index');
    }
}
