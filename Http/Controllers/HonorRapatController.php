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
use Response;

class HonorRapatController extends Controller
{
    public function index(){
        $data = DB::table('view_honor_rapat as h')
            ->join('kegiatan as keg','keg.id_kegiatan','h.id_keg')
            ->get();
        $kegiatan  = DB::table('kegiatan')->get();
        return view ('honorrapat.h_rapat')
        ->with('data', $data)
        ->with('kegiatan', $kegiatan);
    }
    public function list($id){
        $data = DB::table('peserta_rapat')->where('id_honor_rapat',$id)->get();
        $golongan = DB::table('golongan')->get();
        $jumlah = count($data);
        return view ('honorrapat.list')
        ->with('data',$data)
        ->with('golongan',$golongan)
        ->with('jumlah',$jumlah);
    }
    public function store()
    {
        $jml = Input::get('r_jml_peserta');
        DB::table('honor_rapat')->insert(
            array(   
                'id_keg'      => Input::get('r_keg'),
                'acara'       => Input::get('r_acara'),
                'tgl_honor'   => Input::get('r_tgl'),
                'tempat'      => Input::get('r_tempat'),
                'jam_mulai'   => Input::get('r_jam_mulai'),
                'jam_selesai' => Input::get('r_jam_selesai')
            )
        );
        $id = DB::getPdo()->lastInsertId();;
        for ($i=0; $i < $jml ; $i++) { 
            DB::table('peserta_rapat')->insert(
                array(   
                    'id_honor_rapat'      => $id
                )
            );
        }
        Session::flash('message', 'Data Honor Rapat berhasil ditambahkan');
        return Redirect::to('/honorrapat');
    }
    public function list_simpan()
    {
        $jml = Input::get('jmlData');
        for ($i=1; $i <= $jml ; $i++) {
            DB::table('peserta_rapat')->where('id_peserta', Input::get('idpeserta'.$i))->update(
                array(   
                    'golongan'      => Input::get('gol'.$i)
                )
            );
        }
        Session::flash('message', 'Data berhasil disimpan');
        return Redirect::to('/honorrapat');
    }
    public function edit(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('view_honor_rapat')
            ->join('kegiatan','kegiatan.id_kegiatan','view_honor_rapat.id_keg')->where('id_hr','=',$id)->first();
            return Response::json($data);
        }
    }
    public function update()
    {
        $id = Input::get('idhr');
        DB::table('honor_rapat')->where('id_hr',$id)->update(
            array(   
                'id_keg'      => Input::get('er_keg'),
                'acara'       => Input::get('er_acara'),
                'tgl_honor'   => Input::get('er_tgl'),
                'tempat'      => Input::get('er_tempat'),
                'jam_mulai'   => Input::get('er_jam_mulai'),
                'jam_selesai' => Input::get('er_jam_selesai')
            )
        );
        Session::flash('message', 'Data Honor Rapat berhasil dirubah');
        return Redirect::to('/honorrapat');
    }
    public function delete($id)
    {
        DB::table('peserta_rapat')->where('id_honor_rapat',$id)->delete();
        DB::table('honor_rapat')->where('id_hr',$id)->delete();
        Session::flash('message', 'Data Honor Rapat berhasil dihapus');
        return Redirect::to('/honorrapat');
    }
    public function cetak1($id, $keg)
    {
        $peserta = DB::table('peserta_rapat')->where('id_honor_rapat',$id)->get();
        $honorapat = DB::table('honor_rapat')->where('id_hr',$id)->first();
        $kegiatan = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.pptk')
        ->where('id_kegiatan',$keg)->first();
        $ppk = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.ppk')
        ->where('id_kegiatan',$keg)->select('pegawai.nip_pegawai','pegawai.nama_pegawai')->first();
        $bendahara = DB::table('pegawai')->where('bendahara', 1)->select('nip_pegawai','nama_pegawai')->first();
        // dd($bendahara);
        return view ('honorrapat.cetak_list1')
        ->with('peserta',$peserta)
        ->with('kegiatan',$kegiatan)
        ->with('ppk',$ppk)
        ->with('bendahara',$bendahara)
        ->with('honorapat',$honorapat);
    }
    public function cetak2($id, $keg)
    {
        $peserta = DB::table('peserta_rapat')
        ->join('golongan','peserta_rapat.golongan','golongan.id_golongan')
        ->where('id_honor_rapat',$id)->get();
        $honorapat = DB::table('honor_rapat')->where('id_hr',$id)->first();
        $kegiatan = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.pptk')
        ->where('id_kegiatan',$keg)->first();
        $ppk = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.ppk')
        ->where('id_kegiatan',$keg)->select('pegawai.nip_pegawai','pegawai.nama_pegawai')->first();
        $bendahara = DB::table('pegawai')->where('bendahara', 1)->select('nip_pegawai','nama_pegawai')->first();
        // dd($bendahara);
        return view ('honorrapat.cetak_list2')
        ->with('peserta',$peserta)
        ->with('kegiatan',$kegiatan)
        ->with('ppk',$ppk)
        ->with('bendahara',$bendahara)
        ->with('honorapat',$honorapat);
    }
}
