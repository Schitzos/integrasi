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
use \Symfony\Component\HttpFoundation\File\UploadedFile;
use Validator;
use Schema;
use Input;
use Session;
use Redirect;
use View;
use Hash;
use Auth;
use Response;

class SeksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
	public function index(){
        $data = DB::table('seksi as s')
            ->join('bidang as b','s.id_bidang','=','b.id_bidang')
            ->where('id_seksi','<>',0)
            ->get();
        $bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
       return view ('master.seksi')->with('data',$data)->with('bidang',$bidang);
    }
     
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('seksi as s')
                ->join('bidang as b','s.id_bidang','=','b.id_bidang')
                ->where('s.id_seksi','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Nama_Seksi'   => 'required',
            'Nama_Seksi'   => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/seksi')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('seksi')->insert(
                array(   
                    'nama_seksi'    => Input::get('Nama_Seksi'),
                    'id_bidang'     => Input::get('Nama_Bidang')
                ));
        Session::flash('message', 'Data Seksi berhasil ditambahkan');
        return Redirect::to('/seksi');
        }
    }
    public function update()
    {
        $rules = array(
            'Nama_Seksi_Ubah'   => 'required',
            'Nama_Bidang_Ubah'   => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/seksi')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idseksi');
            DB::table('seksi')->where('id_seksi',$id)->update(
                array(   
                    'nama_seksi'  => Input::get('Nama_Seksi_Ubah'),
                    'id_bidang'   => Input::get('Nama_Bidang_Ubah')
                )
            );
            Session::flash('message', 'Data Seksi  berhasil diubah');
            return Redirect::to('/seksi');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('pegawai')->where('id_seksi','=',$id)->count();
        if ($ada != 0) {
            Session::flash('eror', 'Data Seksi tidak dapat dihapus !, karena masih memiliki data Pegawai');   
        } else {
            DB::table('seksi')->where('id_seksi', '=',$id)->delete();
            Session::flash('message', 'Data Seksi berhasil dihapus !');
        }
        return Redirect::to('/seksi');
    }
}

