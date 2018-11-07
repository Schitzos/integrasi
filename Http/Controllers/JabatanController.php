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


class JabatanController extends Controller
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
        $data = DB::table('jabatan')->where('id_jabatan','<>',0)->get();
        return view('master.jabatan')->with('data', $data);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('jabatan')
                ->where('id_jabatan','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Nama_Jabatan' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/jabatan')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('jabatan')->insert(
                array(   
                    'nama_jabatan'      => Input::get('Nama_Jabatan'),
                    'keterangan_jabatan'      => Input::get('Keterangan_Jabatan')
                ));
        Session::flash('message', 'Data Jabatan berhasil ditambahkan');
        return Redirect::to('/jabatan');
        }
    }
    public function update()
    {
        $rules = array(
            'Nama_Jabatan_Ubah'   => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/jabatan')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idjabatan');
            DB::table('jabatan')->where('id_jabatan',$id)->update(
                array(   
                    'nama_jabatan'          => Input::get('Nama_Jabatan_Ubah'),
                    'keterangan_jabatan'    => Input::get('Keterangan_Jabatan_Ubah')
                )
            );
            Session::flash('message', 'Data Jabatan  berhasil diubah');
            return Redirect::to('/jabatan');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('pegawai')
                ->select(DB::raw('COUNT(id_jabatan) as jml'))
                ->where('id_jabatan','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Jabatan tidak dapat dihapus !, karena masih memiliki data Pegawai');   
        } else {
            DB::table('jabatan')->where('id_jabatan', '=',$id)->delete();
            Session::flash('message', 'Data Jabatan berhasil dihapus !');
        }
        return Redirect::to('/jabatan');
    }
}
