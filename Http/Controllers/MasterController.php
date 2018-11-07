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
            ->join('bidang as b','p.id_bidang','=','b.id_bidang')
            ->get();
        $jabatan = DB::table('jabatan')->get();
        $golongan = DB::table('golongan')->get();
        $bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
        return view('master.pegawai')->with('data', $data)->with('jabatan',$jabatan)->with('golongan',$golongan)
            ->with('bidang',$bidang);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
                ->join('jabatan as j','p.id_jabatan','=','j.id_jabatan')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->join('bidang as b','p.id_bidang','=','b.id_bidang')
                ->where('p.nip_pegawai','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Bidang'        => 'required|integer',
            'Jabatan'       => 'required|integer',
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
                    'id_bidang'         => Input::get('Bidang'),
                    'id_jabatan'        => Input::get('Jabatan'),
                    'id_golongan'       => Input::get('Golongan'),
                    'nip_pegawai'       => Input::get('nip_pegawai'),
                    'nama_pegawai'      => Input::get('Nama_Pegawai'),
                    'jabatan_instansi'  => Input::get('Jabatan_Instansi')
                ));
        Session::flash('message', 'Data Pegawai berhasil ditambahkan');
        return Redirect::to('/pegawai');
        }
    }
    public function update()
    {
        $rules = array(
            'Bidang_Ubah'        => 'required|integer',
            'Jabatan_Ubah'       => 'required|integer',
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
                    'id_bidang'         => Input::get('Bidang_Ubah'),
                    'id_jabatan'        => Input::get('Jabatan_Ubah'),
                    'id_golongan'       => Input::get('Golongan_Ubah'),
                    'nip_pegawai'       => Input::get('NIP_Pegawai_Ubah'),
                    'nama_pegawai'      => Input::get('Nama_Pegawai_Ubah'),
                    'jabatan_instansi'  => Input::get('Jabatan_Instansi_Ubah')
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
