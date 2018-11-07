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


class KecamatanController extends Controller
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
        $data = DB::table('kecamatan')->get();
        return view('master.kecamatan')->with('data', $data);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('kecamatan')
                ->where('id_kecamatan','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Nama_Kecamatan' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/kecamatan')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('kecamatan')->insert(
                array(   
                    'nama_kecamatan'      => Input::get('Nama_Kecamatan')
                ));
        Session::flash('message', 'Data Kecamatan berhasil ditambahkan');
        return Redirect::to('/kecamatan');
        }
    }
    public function update()
    {
        $rules = array(
            'Nama_Kecamatan_Ubah'   => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/kecamatan')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idkecamatan');
            DB::table('kecamatan')->where('id_kecamatan',$id)->update(
                array(   
                    'nama_kecamatan'    => Input::get('Nama_Kecamatan_Ubah')
                )
            );
            Session::flash('message', 'Data Kecamatan  berhasil diubah');
            return Redirect::to('/kecamatan');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('desa')
                ->select(DB::raw('COUNT(id_kecamatan) as jml'))
                ->where('id_kecamatan','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Kecamatan tidak dapat dihapus !, karena masih memiliki data Desa');   
        } else {
            DB::table('kecamatan')->where('id_kecamatan', '=',$id)->delete();
            Session::flash('message', 'Data Kecamatan berhasil dihapus !');
        }
        return Redirect::to('/kecamatan');
    }
}
