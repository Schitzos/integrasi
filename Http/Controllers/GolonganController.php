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


class GolonganController extends Controller
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
        $data = DB::table('golongan')->get();
        return view('master.golongan')->with('data', $data);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('golongan')
                ->where('id_golongan','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Nama_Golongan'     => 'required',
            'Pangkat_Golongan'  => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/golongan')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('golongan')->insert(
                array(   
                    'nama_golongan' => Input::get('Nama_Golongan'),
                    'pangkat'       => Input::get('Pangkat_Golongan'),
                    'pajak'         => Input::get('Pajak_Golongan'),
                    'uang_harian'   => Input::get('UangHarian'),
                    'uang_lembur'   => Input::get('UangLembur'),
                    'uang_makan'    => Input::get('UangMakan')
                ));
        Session::flash('message', 'Data Golongan berhasil ditambahkan');
        return Redirect::to('/golongan');
        }
    }
    public function update()
    {
        $rules = array(
            'Nama_Golongan_Ubah'   => 'required',
            'Pangkat_Golongan_Ubah'   => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/golongan')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idgolongan');
            DB::table('golongan')->where('id_golongan',$id)->update(
                array(   
                    'nama_golongan' => Input::get('Nama_Golongan_Ubah'),
                    'pangkat'       => Input::get('Pangkat_Golongan_Ubah'),
                    'pajak'         => Input::get('Pajak_Golongan_Ubah'),
                    'uang_harian'   => Input::get('UangHarianUbah'),
                    'uang_lembur'   => Input::get('UangLemburUbah'),
                    'uang_makan'    => Input::get('UangMakanUbah')
                )
            );
            Session::flash('message', 'Data Golongan  berhasil diubah');
            return Redirect::to('/golongan');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('pegawai')
                ->select(DB::raw('COUNT(id_golongan) as jml'))
                ->where('id_golongan','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Golongan tidak dapat dihapus !, karena masih memiliki data Pegawai');   
        } else {
            DB::table('golongan')->where('id_golongan', '=',$id)->delete();
            Session::flash('message', 'Data Golongan berhasil dihapus !');
        }
        return Redirect::to('/golongan');
    }
}
