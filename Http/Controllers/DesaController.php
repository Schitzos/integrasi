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


class DesaController extends Controller
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
    public function index()
    {
        $data = DB::table('desa as d')
            ->join('kecamatan as k','d.id_kecamatan','=','k.id_kecamatan')
            ->get();
        $kecamatan = DB::table('kecamatan')->get();
        return view('master.desa')->with('data', $data)->with('kecamatan',$kecamatan);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('desa as d')
                ->join('kecamatan as k','d.id_kecamatan','=','k.id_kecamatan')
                ->where('d.id_desa','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Kecamatan' => 'required',
            'Nama_Desa' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/desa')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('desa')->insert(
                array(   
                    'id_kecamatan'      => Input::get('Kecamatan'),
                    'nama_desa'    => Input::get('Nama_Desa')
                ));
        Session::flash('message', 'Data Desa berhasil ditambahkan');
        return Redirect::to('/desa');
        }
    }
    public function update()
    {
        $rules = array(
            'Kecamatan_Ubah'   => 'required',
            'Nama_Desa_Ubah'   => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/desa')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('iddesa');
            DB::table('desa')->where('id_desa',$id)->update(
                array(   
                    'id_kecamatan'  => Input::get('Kecamatan_Ubah'),
                    'nama_desa'     => Input::get('Nama_Desa_Ubah')
                )
            );
            Session::flash('message', 'Data Desa  berhasil diubah');
            return Redirect::to('/desa');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('proyek')
                ->select(DB::raw('COUNT(id_desa) as jml'))
                ->where('id_desa','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Desa tidak dapat dihapus !, karena masih memiliki data Paket');   
        } else {
            DB::table('desa')->where('id_desa', '=',$id)->delete();
            Session::flash('message', 'Data Desa berhasil dihapus !');
        }
        return Redirect::to('/desa');
    }
}
