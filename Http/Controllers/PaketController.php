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
use Carbon\Carbon;


class PaketController extends Controller
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
        if (Auth::user()->admin==1) {
            $data = DB::table('paket as p')
                ->join('jadwal as j','p.id_paket','=','j.id_paket')
                ->join('keuangan as k','p.id_paket','=','k.id_paket')
                ->select('p.id_paket','p.nama_paket',DB::raw('MAX(j.realisasi_jadwal) AS realisasi_jadwal'),DB::raw('MAX(j.rencana_jadwal) AS rencana_jadwal'),
                    DB::raw('MAX(k.realisasi_keuangan) AS realisasi_keuangan'),DB::raw('MAX(k.rencana_keuangan) AS rencana_keuangan'))
                ->groupBy('p.id_paket','p.nama_paket')
                ->get();
            $performa = DB::table('jadwal')
                ->whereIn('minggu_jadwal', function($query){
                    $query->select(DB::raw('MAX(minggu_jadwal)'))->from('jadwal')->groupBy('id_paket');
                })
                ->get();
        } else {
            if(Auth::user()->ppk==1){
                $data = DB::table('paket as p')
                    ->join('jadwal as j','p.id_paket','=','j.id_paket')
                    ->join('keuangan as k','p.id_paket','=','k.id_paket')
                    ->join('dpa as d','p.id_dpa','=','d.id_dpa')
                    ->join('kegiatan as g','d.id_kegiatan','=','g.id_kegiatan')
                    ->select('p.id_paket','p.nama_paket',DB::raw('MAX(j.realisasi_jadwal) AS realisasi_jadwal'),DB::raw('MAX(j.rencana_jadwal) AS rencana_jadwal'),
                        DB::raw('MAX(k.realisasi_keuangan) AS realisasi_keuangan'),DB::raw('MAX(k.rencana_keuangan) AS rencana_keuangan'))
                    ->where('g.ppk','=',Auth::user()->nip_pegawai)
                    ->groupBy('p.id_paket','p.nama_paket')
                    ->get();
                $performa = DB::table('jadwal')->get();
            }elseif (Auth::user()->pptk==1) {
                $data = DB::table('paket as p')
                    ->join('jadwal as j','p.id_paket','=','j.id_paket')
                    ->join('keuangan as k','p.id_paket','=','k.id_paket')
                    ->join('dpa as d','p.id_dpa','=','d.id_dpa')
                    ->join('kegiatan as g','d.id_kegiatan','=','g.id_kegiatan')
                    ->select('p.id_paket','p.nama_paket',DB::raw('MAX(j.realisasi_jadwal) AS realisasi_jadwal'),DB::raw('MAX(j.rencana_jadwal) AS rencana_jadwal'),
                        DB::raw('MAX(k.realisasi_keuangan) AS realisasi_keuangan'),DB::raw('MAX(k.rencana_keuangan) AS rencana_keuangan'))
                    ->where('g.pptk','=',Auth::user()->nip_pegawai)
                    ->groupBy('p.id_paket','p.nama_paket')
                    ->get();
                $performa = DB::table('jadwal')->get();
            }elseif (Auth::user()->ppbj==1) {
                $data = DB::table('paket as p')
                    ->join('jadwal as j','p.id_paket','=','j.id_paket')
                    ->join('keuangan as k','p.id_paket','=','k.id_paket')
                    ->join('dpa as d','p.id_dpa','=','d.id_dpa')
                    ->join('kegiatan as g','d.id_kegiatan','=','g.id_kegiatan')
                    ->select('p.id_paket','p.nama_paket',DB::raw('MAX(j.realisasi_jadwal) AS realisasi_jadwal'),DB::raw('MAX(j.rencana_jadwal) AS rencana_jadwal'),
                        DB::raw('MAX(k.realisasi_keuangan) AS realisasi_keuangan'),DB::raw('MAX(k.rencana_keuangan) AS rencana_keuangan'))
                    ->where('g.ppbj','=',Auth::user()->nip_pegawai)
                    ->groupBy('p.id_paket','p.nama_paket')
                    ->get();
                $performa = DB::table('jadwal')->get();
            }else{
                if(Auth::user()->tipe==2){
                    $str=str_replace('_', ' ',Auth::user()->nip_pegawai);
                    $kontraktore = DB::table('kontraktor')->where('nama_kontraktor','like','%'.$str.'%')->first();
                    $data = DB::table('paket as p')
                        ->join('jadwal as j','p.id_paket','=','j.id_paket')
                        ->join('keuangan as k','p.id_paket','=','k.id_paket')
                        ->join('dpa as d','p.id_dpa','=','d.id_dpa')
                        ->join('kegiatan as g','d.id_kegiatan','=','g.id_kegiatan')
                        ->select('p.id_paket','p.nama_paket',DB::raw('MAX(j.realisasi_jadwal) AS realisasi_jadwal'),DB::raw('MAX(j.rencana_jadwal) AS rencana_jadwal'),
                            DB::raw('MAX(k.realisasi_keuangan) AS realisasi_keuangan'),DB::raw('MAX(k.rencana_keuangan) AS rencana_keuangan'))
                        ->where('p.id_kontraktor','=',$kontraktore->id_kontraktor)
                        ->groupBy('p.id_paket','p.nama_paket')
                        ->get();
                    $performa = DB::table('jadwal')->get();
                }elseif(Auth::user()->tipe==3){
                    $str=str_replace('_', ' ',Auth::user()->nip_pegawai);
                    $konsultane = DB::table('konsultan')->where('nama_konsultan','like','%'.$str.'%')->first();
                    $data = DB::table('paket as p')
                        ->join('jadwal as j','p.id_paket','=','j.id_paket')
                        ->join('keuangan as k','p.id_paket','=','k.id_paket')
                        ->join('dpa as d','p.id_dpa','=','d.id_dpa')
                        ->join('kegiatan as g','d.id_kegiatan','=','g.id_kegiatan')
                        ->select('p.id_paket','p.nama_paket',DB::raw('MAX(j.realisasi_jadwal) AS realisasi_jadwal'),DB::raw('MAX(j.rencana_jadwal) AS rencana_jadwal'),
                            DB::raw('MAX(k.realisasi_keuangan) AS realisasi_keuangan'),DB::raw('MAX(k.rencana_keuangan) AS rencana_keuangan'))
                        ->where('p.id_kontraktor','=',$konsultane->id_konsultan)
                        ->groupBy('p.id_paket','p.nama_paket')
                        ->get();
                    $performa = DB::table('jadwal')->get();
                }
            }
        } 
        return view('paket.paket_list')->with('data', $data)->with('performa',$performa);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('jadwal')
                ->where('id_paket','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showkegiatan(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('kegiatan as k')
                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                ->join('bidang as b','s.id_bidang','=','b.id_bidang')
                ->where('b.id_bidang','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showdpa(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('dpa')
                ->where('id_kegiatan','=',$id)
                ->where('paket','=',1)
                ->get();
            return Response::json($data);
        }
    }
    public function showkordinator(Request $request, $id)
    {
        if ($request->ajax()) {
            $seksi  = DB::table('dpa as d')
                ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                ->where('d.id_dpa','=',$id)->first();
            $data = DB::table('pegawai as p')
                ->where('p.id_seksi','=',$seksi->id_seksi)
                ->where('p.koordinator','=',1)
                ->get();
            return Response::json($data);
        }
    }
    public function showdesa(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('desa')
                ->where('id_kecamatan','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showpphp(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
                ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                ->join('bidang as b','s.id_bidang','=','b.id_bidang')
                ->where('p.pphp','=',1)
                ->get();
            return Response::json($data);
        }
    }
    public function showangpp(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
            ->join('seksi as s','p.id_seksi','=','s.id_seksi')
            ->join('bidang as b','s.id_bidang','=','b.id_bidang')
            ->where('b.id_bidang','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showanggota(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pphp')
                ->where('id_paket','=',$id)
                ->where('status_pphp','=',3)
                ->get();
            return Response::json($data);
        }
    }
    public function create()
    {
        $bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
        $kecamatan = DB::table('kecamatan')->get();
        $kontraktor = DB::table('kontraktor')->get();
        $konsultan = DB::table('konsultan')->get();
        $pegawai = DB::table('pegawai')->where('pphp','=',1)->get();
        return view('paket.create')->with('bidang',$bidang)->with('kecamatan',$kecamatan)->with('kontraktor',$kontraktor)
        ->with('konsultan',$konsultan)->with('pegawai',$pegawai);
    }
    public function store()
    {
        $kontrakbaru = Input::get('Kontraktor_Paket');
        if ($kontrakbaru==null||$kontrakbaru=='') {
            if(Input::get('Nama_Kontraktor')!=null||Input::get('Nama_Kontraktor')!=''){
                DB::table('kontraktor')->insert(
                array(   
                    'nama_kontraktor'                   => Input::get('Nama_Kontraktor'),
                    'telp_kontraktor'                   => Input::get('Telp_Kontraktor'),
                    'alamat_kontraktor'                 => Input::get('Alamat_Kontraktor'),
                    'direktur_kontraktor'               => Input::get('Direktur_Kontraktor'),
                ));
                $traktor = DB::table('kontraktor')->select(DB::raw('MAX(id_kontraktor) as id_kontraktor'))->first();
                $kon = $traktor->id_kontraktor;
                $str=str_replace('.', '',Input::get('Nama_Kontraktor'));
                $str=preg_replace('/\s+/', '_',$str);
                DB::table('pegawai')->insert(
                    array( 
                        'nama_pegawai'      => strtoupper(Input::get('Nama_Kontraktor')),
                        'nip_pegawai'       => strtolower($str),
                        'remember_token'    => Input::get('_token'),
                        'password'          => Hash::make('123456'),
                        'created_at'        => date('Y-m-d H:i:s'),
                        'avatar'            => 'default.png',
                        'tipe'              => 2,
                        'andropass'         => MD5('123456')
                ));
            }else{
                $kon='';
            }
        } else {
            $kon = $kontrakbaru;
        }
        $konsultanbaru = Input::get('Konsultan_Paket');
        if ($konsultanbaru==null||$konsultanbaru=='') {
            if(Input::get('Nama_Konsultan')!=null||Input::get('Nama_Konsultan')!=''){
                DB::table('konsultan')->insert(
                    array(   
                        'nama_konsultan'                   => Input::get('Nama_Konsultan'),
                        'telp_konsultan'                   => Input::get('Telp_Konsultan'),
                        'alamat_konsultan'                 => Input::get('Alamat_Konsultan'),
                        'direktur_konsultan'               => Input::get('Direktur_Konsultan')   
                    ));
                $sultan = DB::table('konsultan')->select(DB::raw('MAX(id_konsultan) as id_konsultan'))->first();
                $sul = $sultan->id_konsultan;
                $str=str_replace('.', '',Input::get('Nama_Konsultan'));
                $str=preg_replace('/\s+/', '_',$str);
                DB::table('users')->insert(
                    array( 
                        'nama_pegawai'      => strtoupper(Input::get('Nama_Konsultan')),
                        'nip_pegawai'       => strtolower($str),
                        'remember_token'    => Input::get('_token'),
                        'password'          => Hash::make('123456'),
                        'created_at'        => date('Y-m-d H:i:s'),
                        'avatar'            => 'default.png',
                        'tipe'              => 3,
                        'andropass'         => MD5('123456')
                ));
            }else{
                $sul='';
            }
        } else {
            $sul = $konsultanbaru;
        }
        $JenisBayar = Input::get('Cara_Bayar');
        if ($JenisBayar==1) {
            $JumlahBayar= Input::get('Jml_Bayar');
        } else {
            $JumlahBayar= 0;
        }
        DB::table('paket')->insert(
            array(   
                'id_desa'           => Input::get('Desa_Paket'),
                'id_dpa'            => Input::get('DPA_Paket'),
                'id_kontraktor'     => $kon,
                'id_konsultan'      => $sul,
                'nama_paket'        => Input::get('Nama_Paket'),
                'nomor_kontrak'     => Input::get('Nomor_Kontrak'),
                'tgl_mulai'         => date('Y-m-d',strtotime(Input::get('from'))),
                'tgl_selesai'       => date('Y-m-d',strtotime(Input::get('to'))),
                'nilai_paket'       => Input::get('Nilai_Kon'),
                'kordinator_paket'  => Input::get('Kordinator_Paket'),
                'no_spmk'           => Input::get('Nomor_SPMK'),
                'tgl_spmk'          => date('Y-m-d',strtotime(Input::get('Tgl_SPMK'))),
                'longi_lokasi'      => Input::get('Latitude'),
                'lati_lokasi'       => Input::get('Longitude'),
                'periode'           => Input::get('Tahun_Anggaran'),
                'jenis_pembayaran'  => $JenisBayar,
                'jumlah_pembayaran' => $JumlahBayar
            ));
        DB::table('dpa')->where('id_dpa','=',Input::get('DPA_Paket'))->update(
            array(   
                'nomor_kontrak'     => Input::get('Nomor_Kontrak'),
                'nilai_kontrak'     => Input::get('Nilai_Kon'),
                'oke_oce'           => 1,
                'mulai'             => date('Y-m-d',strtotime(Input::get('from'))),
                'selesai'           => date('Y-m-d',strtotime(Input::get('to'))),
                'id_kontraktor'     => $kon,
                'id_konsultan'      => $sul
            ));
        $kodepaket = DB::table('paket')->select(DB::raw('MAX(id_paket) as id_paket'))->first();
        $datapaket = DB::table('paket as p')
            ->join('dpa as d','p.id_dpa','=','d.id_dpa')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->where('p.id_paket','=',$kodepaket->id_paket)
            ->first();
        $dtmulai = new Carbon($datapaket->tgl_mulai);
        $dtselesai =new Carbon($datapaket->tgl_selesai);
        $jmlminggu = $dtmulai->diffInWeeks($dtselesai, false);
        if ($jmlminggu<=0) {
            Session::flash('eror', 'Data Paket tidak dapat ditambahkan !, karena tanggal awal kontrak sama atau lebih kecil dari tanggal akhir kontrak');
            DB::table('paket')->where('id_paket','=',$datapaket->id_paket)->delete();
        } else {
            $pphp  = Input::get('PPHP_Proyek');
            if ($pphp==null||$pphp=='') {
                $anggota = Input::get('Anggota_PPHP');
                if($anggota!=null){
                    for ($i=0; $i < count($anggota); $i++) { 
                        DB::table('pphp')->insert(
                        array( 
                            'id_paket'      => $datapaket->id_paket,
                            'nip_pegawai'   => $anggota[$i],
                            'status_pphp'   => 3
                        ));
                    }
                }else{
                    DB::table('pphp')->insert(
                        array( 
                            'id_paket'      => $datapaket->id_paket,
                            'nip_pegawai'   => '',
                            'status_pphp'   => 3
                        ));
                }
                DB::table('pphp')->insert(
                    array( 
                        'id_paket'      => $datapaket->id_paket,
                        'nip_pegawai'   => Input::get('Ketua_PPHP'),
                        'status_pphp'   => 1
                    ));
                DB::table('pphp')->insert(
                    array( 
                        'id_paket'      => $datapaket->id_paket,
                        'nip_pegawai'   => Input::get('Sekertaris_PPHP'),
                        'status_pphp'   => 2
                    ));
            } else {
                DB::table('pphp')->insert(
                    array( 
                        'id_paket'      => $datapaket->id_paket,
                        'nip_pegawai'   => Input::get('PPHP_Proyek'),
                        'status_pphp'   => 0
                    ));
            }
            $hari = $dtmulai->format('l');
                switch ($hari) {
                    case 'Sunday':
                        $dtminggu = $dtmulai->addDays(8);
                        break;
                    case 'Monday':
                        $dtminggu = $dtmulai->addDays(7);
                        break;
                    case 'Tuesday':
                        $dtminggu = $dtmulai->addDays(6);
                        break;
                    case 'Wednesday':
                        $dtminggu = $dtmulai->addDays(5);
                        break;
                    case 'Thursday':
                        $dtminggu = $dtmulai->addDays(4);
                        break;
                    case 'Friday':
                        $dtminggu = $dtmulai->addDays(3);
                        break;
                    case 'Saturday':
                        $dtminggu = $dtmulai->addDays(2);
                        break;
                    default:
                }
            $pathroot = public_path().'/images/dokumentasi/'.$datapaket->id_paket;
            $exists = Storage::disk('dokumentasi')->exists($datapaket->id_paket);
            if (!$exists) {
                File::makeDirectory($pathroot, 0777, true, true);
            }
            for ($i=0; $i <= $jmlminggu; $i++) {
                DB::table('jadwal')->insert(
                array(   
                    'id_paket'         => $datapaket->id_paket,
                    'minggu_jadwal'     => $dtminggu,
                    'ke_jadwal'         => $i+1
                ));
                DB::table('keuangan')->insert(
                array(   
                    'id_paket'         => $datapaket->id_paket,
                    'minggu_keuangan'   => $dtminggu
                ));
                $path = 'images/dokumentasi/'.$datapaket->id_paket.'/foto/Minggu_Ke_'.($i+1);
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true, true);
                }
                if  ($i==$jmlminggu) {
                   switch ($hari) {
                        case 'Sunday':
                            $dtminggu = $dtmulai->addDays(8);
                            break;
                        case 'Monday':
                            $dtminggu = $dtmulai->addDays(7);
                            break;
                        case 'Tuesday':
                            $dtminggu = $dtmulai->addDays(6);
                            break;
                        case 'Wednesday':
                            $dtminggu = $dtmulai->addDays(5);
                            break;
                        case 'Thursday':
                            $dtminggu = $dtmulai->addDays(4);
                            break;
                        case 'Friday':
                            $dtminggu = $dtmulai->addDays(3);
                            break;
                        case 'Saturday':
                            $dtminggu = $dtmulai->addDays(2);
                            break;
                        default:
                    }
                } else {
                    $dtminggu = $dtmulai->addWeeks(1);
                }
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/video/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/kualitas/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/kunjungan/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/rab/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/rks/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/pelaksanaan/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/laporan/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            Session::flash('message', 'Data Paket berhasil ditambahkan');
        }
        return Redirect::to('/paket');
    }
    public function edit($id)
    {
        $data = DB::table('paket as p')
            ->join('dpa as dp','p.id_dpa','=','dp.id_dpa')
            ->join('kegiatan as k','dp.id_kegiatan','=','k.id_kegiatan')
            ->join('seksi as e','k.id_seksi','=','e.id_seksi')
            ->join('program as g','k.id_program','=','g.id_program')
            ->leftJoin('desa as d','p.id_desa','=','d.id_desa')
            ->leftJoin('kecamatan as c','d.id_kecamatan','=','c.id_kecamatan')
            ->leftJoin('kontraktor as r','p.id_kontraktor','=','r.id_kontraktor')
            ->leftJoin('konsultan as s','p.id_konsultan','=','s.id_konsultan')
            ->where('p.id_paket','=',$id)
            ->first();
        $program = DB::table('program')->get();
        $kegiatan = DB::table('kegiatan as k')
            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
            ->where('s.id_bidang','=',$data->id_bidang)
            ->get();
        $dpa = DB::table('dpa')
            ->where('id_kegiatan','=',$data->id_kegiatan)
            ->where('paket','=',1)
            ->get();
        $kecamatan = DB::table('kecamatan')->get();
        $desa = DB::table('desa')->get();
        $kontraktor = DB::table('kontraktor')->get();
        $konsultan = DB::table('konsultan')->get();
        $kordinator  = DB::table('pegawai as p')
                ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                ->where('p.koordinator','=',1)
                ->where('s.id_bidang','=',$data->id_bidang)
                ->get();
        $bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
        $pegawai = DB::table('pegawai')->where('pphp','=',1)->get();
        $adapphp = DB::table('pphp')
            ->select(DB::raw('COUNT(*) AS jml'))
            ->where('id_paket','=',$data->id_paket)->first();
        $jmlpphp = $adapphp->jml;
        if ($jmlpphp >=2) {
            $angpphp = DB::table('pphp')
                ->where('id_paket','=',$data->id_paket)
                ->where('status_pphp','=',3)
                ->get();
            $ketupphp = DB::table('pphp')
                ->where('id_paket','=',$data->id_paket)
                ->where('status_pphp','=',1)
                ->first();
            $sekpphp = DB::table('pphp')
                ->where('id_paket','=',$data->id_paket)
                ->where('status_pphp','=',2)
                ->first();
            return view('edit')->with('data',$data)->with('program',$program)->with('kegiatan',$kegiatan)
            ->with('kecamatan',$kecamatan)->with('desa',$desa)->with('pegawai',$pegawai)
            ->with('kontraktor',$kontraktor)->with('konsultan',$konsultan)->with('kordinator',$kordinator)
            ->with('ketupphp',$ketupphp)->with('sekpphp',$sekpphp)->with('angpphp',$angpphp)
            ->with('jmlpphp',$jmlpphp)->with('dpa',$dpa)->with('bidang',$bidang);
        } else {
            $pphp = DB::table('pphp')->where('id_paket','=',$data->id_paket)->first();
            return view('paket.edit')->with('data',$data)->with('program',$program)->with('kegiatan',$kegiatan)
            ->with('kecamatan',$kecamatan)->with('desa',$desa)->with('pegawai',$pegawai)
            ->with('kontraktor',$kontraktor)->with('konsultan',$konsultan)->with('kordinator',$kordinator)
            ->with('pphp',$pphp)->with('jmlpphp',$jmlpphp)->with('dpa',$dpa)->with('bidang',$bidang);
        }
        
    }
    public function update()
    {
        $id  = Input::get('idpaket');
        $idkon = Input::get('idkontraktor');
        $idsul = Input::get('idkonsultan');
        $kontrakbaru = Input::get('Kontraktor_Paket');
        if ($kontrakbaru==null||$kontrakbaru=='') {
            if(Input::get('Nama_Kontraktor')!=null||Input::get('Nama_Kontraktor')!=''){
                DB::table('kontraktor')->insert(
                array(   
                    'nama_kontraktor'                   => Input::get('Nama_Kontraktor'),
                    'telp_kontraktor'                   => Input::get('Telp_Kontraktor'),
                    'alamat_kontraktor'                 => Input::get('Alamat_Kontraktor'),
                    'direktur_kontraktor'               => Input::get('Direktur_Kontraktor'),
                ));
                $traktor = DB::table('kontraktor')->select(DB::raw('MAX(id_kontraktor) as id_kontraktor'))->first();
                $kon = $traktor->id_kontraktor;
                $str=str_replace('.', '',Input::get('Nama_Kontraktor'));
                $str=preg_replace('/\s+/', '_',$str);
                DB::table('pegawai')->insert(
                    array( 
                        'nama_pegawai'      => strtoupper(Input::get('Nama_Kontraktor')),
                        'nip_pegawai'       => strtolower($str),
                        'remember_token'    => Input::get('_token'),
                        'password'          => Hash::make('123456'),
                        'created_at'        => date('Y-m-d H:i:s'),
                        'avatar'            => 'default.png',
                        'tipe'              => 2,
                        'andropass'         => MD5('123456')
                ));
            }else{
                $kon='';
            }
        } else {
            $kon = $kontrakbaru;
        }
        $konsultanbaru = Input::get('Konsultan_Paket');
        if ($konsultanbaru==null||$konsultanbaru=='') {
            if(Input::get('Nama_Konsultan')!=null||Input::get('Nama_Konsultan')!=''){
                DB::table('konsultan')->insert(
                    array(   
                        'nama_konsultan'                   => Input::get('Nama_Konsultan'),
                        'telp_konsultan'                   => Input::get('Telp_Konsultan'),
                        'alamat_konsultan'                 => Input::get('Alamat_Konsultan'),
                        'direktur_konsultan'               => Input::get('Direktur_Konsultan')   
                    ));
                $sultan = DB::table('konsultan')->select(DB::raw('MAX(id_konsultan) as id_konsultan'))->first();
                $sul = $sultan->id_konsultan;
                $str=str_replace('.', '',Input::get('Nama_Konsultan'));
                $str=preg_replace('/\s+/', '_',$str);
                DB::table('pegawai')->insert(
                    array( 
                        'nama_pegawai'      => strtoupper(Input::get('Nama_Konsultan')),
                        'nip_pegawai'       => strtolower($str),
                        'remember_token'    => Input::get('_token'),
                        'password'          => Hash::make('123456'),
                        'created_at'        => date('Y-m-d H:i:s'),
                        'avatar'            => 'default.png',
                        'tipe'              => 3,
                        'andropass'         => MD5('123456')
                ));
            }else{
                $sul='';
            }
        } else {
            $sul = $konsultanbaru;
        }
        $JenisBayar = Input::get('Cara_Bayar');
        if ($JenisBayar==1) {
            $JumlahBayar= Input::get('Jml_Bayar');
        } else {
            $JumlahBayar= 0;
        }
        $jadwalpaket = DB::table('paket')->where('id_paket','=',$id)->first();
        $tgl1lawas  = $jadwalpaket->tgl_mulai;
        $tgl2lawas  = $jadwalpaket->tgl_selesai;
        DB::table('paket')->where('id_paket','=',$id)->update(
            array(   
                'id_desa'           => Input::get('Desa_Paket'),
                'id_dpa'            => Input::get('DPA_Paket'),
                'id_kontraktor'     => $kon,
                'id_konsultan'      => $sul,
                'nama_paket'        => Input::get('Nama_Paket'),
                'nomor_kontrak'     => Input::get('Nomor_Kontrak'),
                'tgl_mulai'         => date('Y-m-d',strtotime(Input::get('from'))),
                'tgl_selesai'       => date('Y-m-d',strtotime(Input::get('to'))),
                'nilai_paket'       => Input::get('Nilai_Kon'),
                'kordinator_paket'  => Input::get('Kordinator_Paket'),
                'no_spmk'           => Input::get('Nomor_SPMK'),
                'tgl_spmk'          => date('Y-m-d',strtotime(Input::get('Tgl_SPMK'))),
                'longi_lokasi'      => Input::get('Longitude'),
                'lati_lokasi'       => Input::get('Latitude'),
                'periode'           => Input::get('Tahun_Anggaran'),
                'jenis_pembayaran'  => $JenisBayar,
                'jumlah_pembayaran' => $JumlahBayar
            ));
        DB::table('dpa')->where('id_dpa','=',Input::get('DPA_Paket'))->update(
            array(   
                'nilai_kontrak'     => Input::get('Nilai_Kon')
            ));
        $datapaket = DB::table('paket as p')
            ->join('dpa as d','p.id_dpa','=','d.id_dpa')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->where('p.id_paket','=',$id)->first();
        $dtmulai = new Carbon($datapaket->tgl_mulai);
        $dtselesai =new Carbon($datapaket->tgl_selesai);
        $jmlminggu = $dtmulai->diffInWeeks($dtselesai, false);
        if ($jmlminggu<=0) {
            Session::flash('eror', 'Data Paket tidak dapat diubah !, karena tanggal awal kontrak sama atau lebih kecil dari tanggal akhir kontrak');   
        } else {
            DB::table('pphp')->where('id_paket','=',$id)->delete();
            $pphp  = Input::get('PPHP_Proyek');
            if ($pphp==null||$pphp=='') {
                $anggota = Input::get('Anggota_PPHP_Ubah');
                if($anggota!=null||$anggota!=''){
                    for ($i=0; $i < count($anggota); $i++) { 
                        DB::table('pphp')->insert(
                        array( 
                            'id_paket'            => $datapaket->id_paket,
                            'nip_pegawai'          => $anggota[$i],
                            'status_pphp'                 => 3
                        ));
                    }
                }
                DB::table('pphp')->insert(
                    array( 
                        'id_paket'            => $datapaket->id_paket,
                        'nip_pegawai'          => Input::get('Ketua_PPHP'),
                        'status_pphp'                 => 1
                    ));
                DB::table('pphp')->insert(
                    array( 
                        'id_paket'            => $datapaket->id_paket,
                        'nip_pegawai'          => Input::get('Sekertaris_PPHP'),
                        'status_pphp'                 => 2
                    ));
            } else {
                DB::table('pphp')->insert(
                    array( 
                        'id_paket'            => $datapaket->id_paket,
                        'nip_pegawai'          => Input::get('PPHP_Proyek'),
                        'status_pphp'                 => 0
                    ));
            }
            if ($tgl1lawas!=date('Y-m-d',strtotime(Input::get('from')))) {
                DB::table('jadwal')->where('id_paket','=',$id)->delete();
                DB::table('keuangan')->where('id_paket','=',$id)->delete();
                $hari = $dtmulai->format('l');
                switch ($hari) {
                    case 'Sunday':
                        $dtminggu = $dtmulai->addDays(8);
                        break;
                    case 'Monday':
                        $dtminggu = $dtmulai->addDays(7);
                        break;
                    case 'Tuesday':
                        $dtminggu = $dtmulai->addDays(6);
                        break;
                    case 'Wednesday':
                        $dtminggu = $dtmulai->addDays(5);
                        break;
                    case 'Thursday':
                        $dtminggu = $dtmulai->addDays(4);
                        break;
                    case 'Friday':
                        $dtminggu = $dtmulai->addDays(3);
                        break;
                    case 'Saturday':
                        $dtminggu = $dtmulai->addDays(2);
                        break;
                    default:
                }
                for ($i=0; $i < $jmlminggu; $i++) {
                    DB::table('jadwal')->insert(
                    array(   
                        'id_paket'         => $datapaket->id_paket,
                        'minggu_jadwal'     => $dtminggu,
                        'ke_jadwal'         => $i+1
                    ));
                    DB::table('keuangan')->insert(
                    array(   
                        'id_paket'         => $datapaket->id_paket,
                        'minggu_keuangan'   => $dtminggu
                    ));
                    $path = 'images/dokumentasi/'.$datapaket->id_paket.'/foto/Minggu_Ke_'.($i+1);
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0777, true, true);
                    }
                    if  ($i==$jmlminggu-1) {
                       switch ($hari) {
                            case 'Sunday':
                                $dtminggu = $dtmulai->addDays(8);
                                break;
                            case 'Monday':
                                $dtminggu = $dtmulai->addDays(7);
                                break;
                            case 'Tuesday':
                                $dtminggu = $dtmulai->addDays(6);
                                break;
                            case 'Wednesday':
                                $dtminggu = $dtmulai->addDays(5);
                                break;
                            case 'Thursday':
                                $dtminggu = $dtmulai->addDays(4);
                                break;
                            case 'Friday':
                                $dtminggu = $dtmulai->addDays(3);
                                break;
                            case 'Saturday':
                                $dtminggu = $dtmulai->addDays(2);
                                break;
                            default:
                        }
                    } else {
                        $dtminggu = $dtmulai->addWeeks(1);
                    }
                }
            }
            $pathroot = public_path().'/images/dokumentasi/'.$datapaket->id_paket;
            $exists = Storage::disk('dokumentasi')->exists($datapaket->id_paket);
            if (!$exists) {
                File::makeDirectory($pathroot, 0777, true, true);
            }
            
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/video/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/kualitas/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/kunjungan/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/rab/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/rks/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/pelaksanaan/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $path = 'images/dokumentasi/'.$datapaket->id_paket.'/laporan/';
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            Session::flash('message', 'Data Paket berhasil diubah');
        }
        return Redirect::to('/paket');
    }
    public function destroy($id)
    {
        $pathroot = public_path().'/images/dokumentasi/'.$id.'/';
        //hapus folder sak file pakete
        if (File::exists($pathroot)) {
            File::delete($pathroot);
        }
        //hapus data dokumentasi pakete
        $katkunjungan = DB::table('kategori_kunjungan')->where('id_paket','=',$id)->get();
        foreach ($katkunjungan as $k) {
            DB::table('kunjungan')->where('id_kategori_kunjungan', '=',$k->id_kategori_kunjungan)->delete();
        }
        $katkualitas = DB::table('kategori_kualitas')->where('id_paket','=',$id)->get();
        foreach ($katkualitas as $k) {
            DB::table('kualitas')->where('id_kategori_kualitas', '=',$k->id_kategori_kualitas)->delete();
        }
        $katvideo = DB::table('kategori_video')->where('id_paket','=',$id)->get();
        foreach ($katvideo as $k) {
            DB::table('video')->where('id_kategori_video', '=',$k->id_kategori_video)->delete();
        }
        //hapus data RAB pakete
        $jenisrab = DB::table('jenis_rab')->where('id_paket','=',$id)->get();
        foreach ($jenisrab as $j) {
            $rabpro = DB::table('rab_paket')->where('id_jenis_rab', '=',$j->id_jenis_rab)->get();
            foreach ($rabpro as $r) {
                DB::table('detail_rab')->where('id_rab_paket', '=',$r->id_rab_paket)->delete();
                DB::table('detail_rencana')->where('id_rab_paket', '=',$r->id_rab_paket)->delete();
            }
            DB::table('rab_paket')->where('id_jenis_rab', '=',$j->id_jenis_rab)->delete();
        }
        DB::table('jenis_rab')->where('id_paket', '=',$id)->delete();
        //hapus data root pakete
        $jadwal = DB::table('jadwal')->where('id_paket', '=',$id)->get();
        foreach ($jadwal as $j) {
            DB::table('foto')->where('id_jadwal', '=',$j->id_jadwal)->delete();
        }
        DB::table('jadwal')->where('id_paket', '=',$id)->delete();
        DB::table('keuangan')->where('id_paket', '=',$id)->delete();
        DB::table('pphp')->where('id_paket', '=',$id)->delete();
        DB::table('paket')->where('id_paket', '=',$id)->delete();
        Session::flash('message', 'Data Paket berhasil dihapus !');
        return Redirect::to('/paket');
    }
    public function showgrafik(Request $request, $id)
    {
        if ($request->ajax()) {
            $base = DB::table('detail_rab AS d')
                ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab AS jr','r.id_jenis_rab','=','jr.id_jenis_rab')
                ->join('paket AS p','jr.id_paket','=','p.id_paket')
                ->join('jadwal AS j','d.minggu_ke','=','j.ke_jadwal')
                ->select('p.id_paket','j.minggu_jadwal AS minggu','r.pekerjaan_rab_paket AS item ',
                    'r.harga_rab_paket AS harga_satuan','d.isi_detail_rab AS volume',DB::raw('d.isi_detail_rab * r.harga_rab_paket AS harga_total'),DB::raw('r.volume_rab_paket * r.harga_rab_paket AS nilai_kontrak'))
                ->where('p.id_paket','=',$id)
                ->where('j.id_paket','=',$id)
                ->get();
            $rencana = DB::table('detail_rencana AS d')
                ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab AS jr','r.id_jenis_rab','=','jr.id_jenis_rab')
                ->join('paket AS p','jr.id_paket','=','p.id_paket')
                ->join('jadwal AS j','d.minggu_ke_rencana','=','j.ke_jadwal')
                ->select('p.id_paket','j.minggu_jadwal AS minggu','r.pekerjaan_rab_paket AS item ',
                    'r.harga_rab_paket AS harga_satuan','d.isi_detail_rencana AS volume',DB::raw('d.isi_detail_rencana * r.harga_rab_paket AS harga_total'),DB::raw('r.volume_rab_paket * r.harga_rab_paket AS nilai_kontrak'))
                ->where('p.id_paket','=',$id)
                ->where('j.id_paket','=',$id)
                ->get();
            $jadwal = DB::table('jadwal')->where('id_paket','=',$id)->get();
            $data =  array();
            $prosen = 0;
            $prosen1 = 0;
            foreach ($jadwal as $j) {
                $data[$j->minggu_jadwal]['minggu']=$j->minggu_jadwal;
                $total =0;
                $kontrak =0;
                $totalrencana=0;
                $kontrakrencana=0;
                foreach ($base as $value) {
                    if ($value->minggu==$j->minggu_jadwal) {
                        $total += $value->harga_total;
                        $kontrak += $value->nilai_kontrak;
                    }
                }
                foreach ($rencana as $value) {
                    if ($value->minggu==$j->minggu_jadwal) {
                        $totalrencana += $value->harga_total;
                    }   
                }
                $data[$j->minggu_jadwal]['total'] = $total;
                $data[$j->minggu_jadwal]['kontrak'] = $kontrak;
                $data[$j->minggu_jadwal]['rencana'] = $totalrencana;
                if($kontrak!=0){
                $prosen += ($total/$kontrak)*100;
                $prosen1 += ($totalrencana/$kontrak)*100;
                }else{
                    $prosen+=0;
                    $prosen1+=0;
                }
                DB::table('jadwal')->where('id_jadwal','=',$j->id_jadwal)->update(
                    array(
                        'realisasi_jadwal' =>$prosen,
                        'rencana_jadwal' =>$prosen1,
                    )
                );
            }
            return Response::json($data);
        }
    }
    public function showperforma(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('jadwal')
                ->where('id_paket','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showslider(Request $request, $id)
    {
        if ($request->ajax()) {
            $base = DB::table('paket')->where('id_paket','=',$id)->first();
            $startTimeStamp = strtotime($base->tgl_mulai);
            $endTimeStamp = strtotime($base->tgl_selesai);
            $timeDiff = abs($endTimeStamp - $startTimeStamp);
            $max = $timeDiff/86400;
            $max = intval($max);
            $max +=1;
            $startTimeStamp1 = strtotime($base->tgl_mulai);
            $endTimeStamp1 = strtotime(date('Y-m-d'));
            $timeDiff1 = abs($endTimeStamp1 - $startTimeStamp1);
            $sekarang = $timeDiff1/86400;
            $sekarang = intval($sekarang);
            $data =  array('max' => $max, 'sekarang' => $sekarang);
            return Response::json($data);
        }
    }
}
