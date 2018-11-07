<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use \Illuminate\Database\Eloquent\Model;
use Validator;
use Schema;
use Input;
use Session;
use Redirect;
use View;
use Hash;
use Auth;
use Response;
use Carbon\Carbon;


class UangRealisasiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $realisasi = DB::table('paket as p')
            ->join('uang_realisasi as u','p.id_paket','u.id_paket')
            ->where('p.id_paket','=',$id)
            ->get();
        $data = DB::table('paket')
            ->where('id_paket','=',$id)
            ->first();
        return view('detail.realisasiuang')->with('data', $data)->with('realisasi', $realisasi);
    }

    public function store()
    {
        $idpaket = Input::get('idpaket');
        DB::table('uang_realisasi')->insert(
            array( 
                'id_paket'     => Input::get('idpaket'),
                'uraian'        => Input::get('uraian'),
                'nominal'       => Input::get('nom'),
                'retensi'       => Input::get('ret')
            ));
        return Redirect::to('/realisasi/'.$idpaket);
    }

    public function update()
    {
        $idpaket = Input::get('idpaketubah');
        $id = Input::get('idreal');
        DB::table('uang_realisasi')->where('id_uang_realisasi',$id)->update(
            array( 
                'id_paket'     => Input::get('idpaketubah'),
                'uraian'        => Input::get('uraian_ubah'),
                'nominal'       => Input::get('nom_ubah'),
                'retensi'       => Input::get('ret_ubah')
            ));
        return Redirect::to('/realisasi/'.$idpaket);
    }

    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('uang_realisasi')
                ->where('id_paket','=',$id)
                ->get();
            return Response::json($data);
        }
    }
}
