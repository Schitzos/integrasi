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


class KontraktorController extends Controller
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
        $data = DB::table('kontraktor')->get();
        return view('master.kontraktor')->with('data', $data);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('kontraktor')
                ->where('id_kontraktor','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Nama_Kontraktor'   => 'required',
            'Telp_Kontraktor'   => 'required',
            'Alamat_Kontraktor' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/maskon')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('kontraktor')->insert(
                array(   
                    'nama_kontraktor'                   => Input::get('Nama_Kontraktor'),
                    'telp_kontraktor'                   => Input::get('Telp_Kontraktor'),
                    'alamat_kontraktor'                 => Input::get('Alamat_Kontraktor'),
                    'direktur_kontraktor'               => Input::get('Direktur'),
                ));
        Session::flash('message', 'Data Kontraktor berhasil ditambahkan');
        return Redirect::to('/maskon');
        }
    }
    public function update()
    {
        $rules = array(
            'Nama_Kontraktor_Ubah'   => 'required',
            'Telp_Kontraktor_Ubah'   => 'required',
            'Alamat_Kontraktor_Ubah' => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/maskon')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idkontraktor');
            DB::table('kontraktor')->where('id_kontraktor',$id)->update(
                array(   
                    'nama_kontraktor'       => Input::get('Nama_Kontraktor_Ubah'),
                    'telp_kontraktor'       => Input::get('Telp_Kontraktor_Ubah'),
                    'alamat_kontraktor'     => Input::get('Alamat_Kontraktor_Ubah'),
                    'direktur_kontraktor'   => Input::get('Direktur_Ubah'),
                )
            );
            Session::flash('message', 'Data Kontraktor berhasil diubah');
            return Redirect::to('/maskon');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('paket')
                ->select(DB::raw('COUNT(id_kontraktor) as jml'))
                ->where('id_kontraktor','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Kontraktor tidak dapat dihapus !, karena masih memiliki data Paket');   
        } else {
            DB::table('kontraktor')->where('id_kontraktor', '=',$id)->delete();
            Session::flash('message', 'Data Kontraktor berhasil dihapus !');
        }
        return Redirect::to('/maskon');
    }
}
