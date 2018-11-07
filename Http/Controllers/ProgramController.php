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


class ProgramController extends Controller
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
        $data = DB::table('program')->get();
        return view('master.program')->with('data', $data);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('program')
                ->where('id_program','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'id_program' => 'required|unique:program',
            'Nama_Program' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.',
            'unique'    => 'Kolom Kode Program sudah ada, masukkan yang lain.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/program')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('program')->insert(
                array(
                    'id_program'    => Input::get('id_program'),
                    'nama_program'  => Input::get('Nama_Program')
                ));
        Session::flash('message', 'Data Program berhasil ditambahkan');
        return Redirect::to('/program');
        }
    }
    public function update()
    {
        $rules = array(
            'Kode_Program'        => 'required',
            'Nama_Program_Ubah'   => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/program')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idprogram');
            DB::table('program')->where('id_program',$id)->update(
                array(
                    'id_program'    => Input::get('Kode_Program'),
                    'nama_program'  => Input::get('Nama_Program_Ubah')
                )
            );
            Session::flash('message', 'Data Program  berhasil diubah');
            return Redirect::to('/program');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('kegiatan')
                ->select(DB::raw('COUNT(id_program) as jml'))
                ->where('id_program','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Program tidak dapat dihapus !, karena masih memiliki data Kegiatan');   
        } else {
            DB::table('program')->where('id_program', '=',$id)->delete();
            Session::flash('message', 'Data Program berhasil dihapus !');
        }
        return Redirect::to('/program');
    }
}
