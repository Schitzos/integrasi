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


class KonsultanController extends Controller
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
        $data = DB::table('konsultan')->get();
        return view('master.konsultan')->with('data', $data);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('konsultan')
                ->where('id_konsultan','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Nama_Konsultan'    => 'required',
            'Telp_Konsultan'    => 'required',
            'Alamat_Konsultan'  => 'required',
            'Direktur'          => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/sultan')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('konsultan')->insert(
                array(   
                    'nama_konsultan'                   => Input::get('Nama_Konsultan'),
                    'telp_konsultan'                   => Input::get('Telp_Konsultan'),
                    'alamat_konsultan'                 => Input::get('Alamat_Konsultan'),
                    'direktur_konsultan'               => Input::get('Direktur')
                ));
        Session::flash('message', 'Data Konsultan berhasil ditambahkan');
        return Redirect::to('/sultan');
        }
    }
    public function update()
    {
        $rules = array(
            'Nama_Konsultan_Ubah'   => 'required',
            'Telp_Konsultan_Ubah'   => 'required',
            'Alamat_Konsultan_Ubah' => 'required',
            'Direktur_Ubah'          => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/sultan')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idkonsultan');
            DB::table('konsultan')->where('id_konsultan',$id)->update(
                array(   
                    'nama_konsultan'                   => Input::get('Nama_Konsultan_Ubah'),
                    'telp_konsultan'                   => Input::get('Telp_Konsultan_Ubah'),
                    'alamat_konsultan'                 => Input::get('Alamat_Konsultan_Ubah'),
                    'direktur_konsultan'               => Input::get('Direktur_Ubah')
                )
            );
            Session::flash('message', 'Data Konsultan  berhasil diubah');
            return Redirect::to('/sultan');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('paket')
                ->select(DB::raw('COUNT(id_konsultan) as jml'))
                ->where('id_konsultan','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Konsultan tidak dapat dihapus !, karena masih memiliki data Paket');   
        } else {
            DB::table('konsultan')->where('id_konsultan', '=',$id)->delete();
            Session::flash('message', 'Data Konsultan berhasil dihapus !');
        }
        return Redirect::to('/sultan');
    }
}
