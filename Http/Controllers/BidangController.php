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

class BidangController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
	public function index(){
        $data = DB::table('bidang')
            ->where('id_bidang','<>',0)->get();
        // $pegawai = DB::table('pegawai')->where('nip_pegawai','<>',"0")->get();
        return view ('master.bidang')->with('data',$data);
        // ->with('pegawai',$pegawai);
    }
     
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('bidang')
                ->where('id_bidang','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Nama_Bidang'   => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/bidang')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('bidang')->insert(
                array(   
                    'nama_bidang'   => Input::get('Nama_Bidang')
                ));
        Session::flash('message', 'Data Bidang berhasil ditambahkan');
        return Redirect::to('/bidang');
        }
    }
    public function update()
    {
        $rules = array(
            'Nama_Bidang_Ubah'   => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/bidang')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idbidang');
            DB::table('bidang')->where('id_bidang',$id)->update(
                array(   
                    'nama_bidang'   => Input::get('Nama_Bidang_Ubah')
                )
            );
            Session::flash('message', 'Data Bidang  berhasil diubah');
            return Redirect::to('/bidang');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('seksi')
                ->select(DB::raw('COUNT(id_bidang) as jml'))
                ->where('id_bidang','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Bidang tidak dapat dihapus !, karena masih memiliki data Seksi');   
        } else {
            DB::table('bidang')->where('id_bidang', '=',$id)->delete();
            Session::flash('message', 'Data Bidang berhasil dihapus !');
        }
        return Redirect::to('/bidang');
    }
}

