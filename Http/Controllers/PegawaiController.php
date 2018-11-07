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


class PegawaiController extends Controller
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
        $data = DB::table('pegawai as p')
            ->join('jabatan as j','p.id_jabatan','=','j.id_jabatan')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->leftjoin('bidang as b','p.id_bidang','=','b.id_bidang')
            ->leftjoin('seksi as s','p.id_seksi','=','s.id_seksi')
            ->orderby('b.id_bidang')
            ->orderby('s.id_seksi')
            ->get();
        $jabatan = DB::table('jabatan')->where('id_jabatan','<>',0)->get();
        $jabut = DB::table('jabatan_user')->get();  
        $golongan = DB::table('golongan')->get();
        $bidang = DB::table('bidang')->get();
        $seksi = DB::table('seksi')->get();
        return view('master.pegawai')->with('data', $data)->with('bidang', $bidang)->with('jabatan', $jabatan)->with('jabut', $jabut)->with('golongan',$golongan)->with('seksi',$seksi);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai')
                ->where('nip_pegawai','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showbidang(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('bidang')
                ->where('id_bidang','<>',0)
                ->get();
            return Response::json($data);
        }
    }
    public function showseksi(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('seksi')
                ->where('id_bidang','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Jabdin'        => 'required|integer',
            'Golongan'      => 'required|integer',
            'nip_pegawai'   => 'required|unique:pegawai',
            'Nama_Pegawai'  => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.',
            'unique'  => 'Kolom Kode Kegiatan sudah ada, masukkan yang lain.',
            'integer'  => 'Kolom :attribute harus di pilih.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/pegawai')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('pegawai')->insert(
                array(   
                    'id_jabatan'        => Input::get('Jabdin'),
                    'id_bidang'         => Input::get('Bidang-Pegawai'),
                    'id_seksi'          => Input::get('Seksi-Pegawai'),
                    'id_golongan'       => Input::get('Golongan'),
                    'admin'             => Input::get('ckadmin'),
                    'ppk'               => Input::get('ckppk'),
                    'pptk'              => Input::get('ckpptk'),
                    'ppbj'              => Input::get('ckppbj'),
                    'pphp'              => Input::get('ckpphp'),
                    'bendahara'        => Input::get('ckbenda'),
                    'koordinator'       => Input::get('ckkor'),
                    'spj'               => Input::get('ckspj'),
                    'p1'                => Input::get('ckp1'),
                    'nip_pegawai'       => Input::get('nip_pegawai'),
                    'nama_pegawai'      => Input::get('Nama_Pegawai'),
                    'password'          => Hash::make('123456'),
                    'andropas'          => MD5('123456'),
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                    'avatar'            => 'default.png',
                    'tipe'              => 1
                ));
        Session::flash('message', 'Data Pegawai berhasil ditambahkan');
        return Redirect::to('/pegawai');
        }
    }
    public function update()
    {
        $rules = array(
            'Jabdin_Ubah'       => 'required|integer',
            'Golongan_Ubah'      => 'required|integer',
            'NIP_Pegawai_Ubah'   => 'required',
            'Nama_Pegawai_Ubah'  => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/pegawai')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idpegawai');
            DB::table('pegawai')->where('nip_pegawai',$id)->update(
                array(   
                    'id_jabatan'        => Input::get('Jabdin_Ubah'),
                    'admin'             => Input::get('ckadminubah'),
                    'ppk'               => Input::get('ckppkubah'),
                    'pptk'              => Input::get('ckpptkubah'),
                    'ppbj'              => Input::get('ckppbjubah'),
                    'pphp'              => Input::get('ckpphpubah'),
                    'bendahara'         => Input::get('ckbendaubah'),
                    'koordinator'       => Input::get('ckkorubah'),
                    'spj'               => Input::get('ckspjubah'),
                    'p1'                => Input::get('ckp1ubah'),
                    'id_bidang'         => Input::get('Bidang-Pegawai_Ubah'),
                    'id_seksi'          => Input::get('Seksi-Pegawai_Ubah'),
                    'id_golongan'       => Input::get('Golongan_Ubah'),
                    'nip_pegawai'       => Input::get('NIP_Pegawai_Ubah'),
                    'nama_pegawai'      => Input::get('Nama_Pegawai_Ubah')
                )
            );
            Session::flash('message', 'Data Pegawai  berhasil diubah');
            return Redirect::to('/pegawai');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('kegiatan')
                ->select(DB::raw('COUNT(ppk) as jml'))
                ->where('ppk','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Pegawai tidak dapat dihapus !, karena masih memiliki data Kegiatan');   
        } else {
            DB::table('pegawai')->where('nip_pegawai', '=',$id)->delete();
            Session::flash('message', 'Data Pegawai berhasil dihapus !');
        }
        return Redirect::to('/pegawai');
    }
}
