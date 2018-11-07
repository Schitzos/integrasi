<?php namespace App\Http\Controllers;

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
use Auth;
use \PDF;
use App;
use Response;

class DalamController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->admin==1) {
            $data = DB::table('perjalanan as p')
                ->join('kegiatan as k','p.id_kegiatan','=','k.id_kegiatan')
                ->join('pegawai as g','p.pegawai_tertugas','=','g.nip_pegawai')
                ->where('p.tipe_perjalanan','=',0)
                ->get();
        } else {
            $data = DB::table('perjalanan as p')
                ->join('kegiatan as k','p.id_kegiatan','=','k.id_kegiatan')
                ->join('pegawai as g','p.pegawai_tertugas','=','g.nip_pegawai')
                ->join('seksi as s','g.id_seksi','=','s.id_seksi')
                ->where('s.id_bidang','=',Auth::user()->id_bidang)
                ->where('p.tipe_perjalanan','=',0)
                ->get();
        }
        $bulan = DB::table('bulan')->get();
        return view('dalam.index')->with('data',$data)->with('bulan',$bulan);
    }
    public function showkegiatan(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
                ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                ->join('bidang as b','s.id_bidang','=','b.id_bidang')
                ->join('kegiatan as k','s.id_seksi','=','k.id_seksi')
                ->where('k.id_kegiatan','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->where('p.nip_pegawai','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showdataubah(Request $request, $id, $ig)
    {
        if ($request->ajax()) {
            $data = DB::table('pengikut_perjalanan as j')
                ->join('pegawai as p','j.nip_pengikut','=','p.nip_pegawai')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->where('j.kode_perjalanan','=',$id)
                ->where('p.nip_pegawai','=',$ig)
                ->first();
            return Response::json($data);
        }
    }
    public function showpengikut(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pengikut_perjalanan')
                ->where('kode_perjalanan','=',$id)
                ->get();
            return Response::json($data);
        }
    }

    public function cetak($id)
    {
        $data =DB::table('perjalanan as p')
            ->join('pegawai as p1','p.pegawai_tertugas','=','p1.nip_pegawai')
            ->join('kegiatan as k','p.id_kegiatan','=','k.id_kegiatan')
            // ->join('pegawai as p2','k.pptk','=','p2.nip_pegawai')
            ->join('jabatan as j','p1.id_jabatan','=','j.id_jabatan')
            ->join('golongan as g','p1.id_golongan','=','g.id_golongan')
            // ->join('desa as d','p.id_desa','=','d.id_desa')
            ->join('seksi as s','p1.id_seksi','=','s.id_seksi')
            // ->join('bidang as b','s.id_bidang','=','b.id_bidang')
            // ->join('kecamatan as c','d.id_kecamatan','=','c.id_kecamatan')
            ->select('p.*','k.*','g.*','j.*','s.*','p1.nip_pegawai as nip_tertugas','p1.nama_pegawai as nama_tertugas','p1.nip_pegawai as nip_pptk',
                'p1.nama_pegawai','p1.jabatan_instansi','p1.id_jabatan')
            ->where('p.id_perjalanan','=',$id)
            ->first();
        $pengikut = DB::table('pengikut_perjalanan as pp')
            ->join('pegawai as pg','pp.nip_pengikut','=','pg.nip_pegawai')
            ->join('golongan as g','pg.id_golongan','=','g.id_golongan')
            ->join('jabatan as j','pg.id_jabatan','=','j.id_jabatan')
            ->where('pp.kode_perjalanan','=',$id)
            ->where('pp.yg_tugas','!=',1)
            ->orderby('g.id_golongan')
            ->get();
        $menugaskan = DB::table('pengikut_perjalanan as pp')
            ->join('pegawai as pg','pp.nip_pengikut','=','pg.nip_pegawai')
            ->join('golongan as g','pg.id_golongan','=','g.id_golongan')
            ->join('jabatan as j','pg.id_jabatan','=','j.id_jabatan')
            ->join('bidang as b','pg.id_bidang','=','b.id_bidang')
            ->leftjoin('seksi as s','pg.id_seksi','=','s.id_seksi')
            ->where('pp.kode_perjalanan','=',$id)
            ->orderby('g.id_golongan')
            ->get();
        $hasil = DB::table('hasil_perjalanan')
            ->where('id_perjalanan','=',$id)
            ->get();
        $rincian = DB::table('pengikut_perjalanan as pp')
            ->join('perjalanan as j','pp.kode_perjalanan','=','j.id_perjalanan')
            ->join('pegawai as p','pp.nip_pengikut','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->select('g.nama_golongan',DB::raw('COUNT(p.id_golongan) AS jumlah'),'j.lama_perjalanan','pp.uang_harian_pengikut')
            ->where('j.id_perjalanan','=',$data->id_perjalanan)
            ->groupBy('g.id_golongan')
            ->groupBy('g.nama_golongan')
            ->groupBy('j.lama_perjalanan')
            ->groupBy('pp.uang_harian_pengikut')
            ->get();
        $ttd1 =DB::table('perjalanan as p')
            ->join('pegawai as p1','p.ttd','=','p1.nip_pegawai')
            ->where('p.id_perjalanan',$id)->first();
        $ttd2 =DB::table('perjalanan as p')
            ->join('pegawai as p1','p.ttd','=','p1.nip_pegawai')
            ->join('bidang as b','p1.id_bidang','=','b.id_bidang')
            ->where('p.id_perjalanan',$id)->first();
        $ppk =  DB::table('pegawai as p')
            ->join('bidang as b','p.id_bidang','=','b.id_bidang')
            ->join('jabatan as j','p.id_jabatan','=','j.id_jabatan')
            ->where('nip_pegawai',$data->ppk)
            ->first();
        $bendahara  = DB::table('pegawai as p')
            ->where('bendahara','1')
            ->first();
        $nilai = 0;
        foreach ($menugaskan as $value) {
            $nilai+=$value->uang_harian_pengikut*$data->lama_perjalanan;
        }
        $bilang = $this->terbilang($nilai);
        $nom = explode('/', $data->nomor_perjalanan);
        $nomor = $nom[1];
        $tahun = $nom[3];
        $view = View::make('dalam.cetak', array('data' => $data, 'pengikut' => $pengikut, 'ttd2' => $ttd2, 'ttd1' => $ttd1, 'nomor' => $nomor, 
            'tahun' => $tahun, 'bilang' => $bilang, 'nilai' => $nilai, 'rincian' => $rincian, 'ppk' => $ppk, 'bendahara' => $bendahara, 'hasil' => $hasil, 'i' => 0, 'menugaskan' => $menugaskan))->render(); 
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper([0, 0, 609.449, 935.433], 'potrait');
        $pdf->loadHTML($view);
        return $pdf->stream();
    }

    public function create()
    {
        if (Auth::user()->admin==1) {
            $pegawai = DB::table('pegawai')->where('nip_pegawai','<>',"0")->get();
            $kegiatan = DB::table('dpa as d')
                ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                ->where('d.paket','=',2)
                ->get();
        } else {
            if(Auth::user()->pptk==1){
                $pegawai = DB::table('pegawai as p')
                    ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->where('nip_pegawai','<>','0')->get();
                $kegiatan = DB::table('dpa as d')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('d.paket','=',2)
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->get();
            }else{
                $pegawai = DB::table('pegawai as p')
                    ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->where('nip_pegawai','<>','0')->get();
                $kegiatan = DB::table('dpa as d')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('d.paket','=',2)
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->get();
            }
        }
        $ttd = DB::table('pegawai')->whereIn('id_jabatan',[2,1])->get();
        $lokasi = DB::table('desa')->get();
        return view('dalam.create')->with('pegawai',$pegawai)->with('kegiatan',$kegiatan)->with('lokasi',$lokasi)->with('ttd',$ttd);
    }

    public function store()
    {
        $rules = array(
            'Tertugas'          => 'required',
            'Tugas'             => 'required',
            'Tujuan'            => 'required',
            'Kegiatan'          => 'required',
            'Alat'              => 'required',
            'Tgl_Perjalanan'    => 'required',
            'tgl_sp'            => 'required',
            'ttd'            => 'required'
        );
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()){   
            return Redirect::to('/dalam')->withErrors($validator)->withInput();
        }
        else{
            $tgl= explode(" s/d ",Input::get('Tgl_Perjalanan'));
            $tglmulai = $tgl[0];
            $tglselesai = $tgl[1];
            $nom = Input::get('No_Surat');
            DB::table('perjalanan')->insert(
            array( 
                'nomor_perjalanan'  => '094/'.$nom.'/437.86/'.date('Y'),
                'pegawai_tertugas'  => Input::get('Tertugas'),
                'id_kegiatan'       => Input::get('Kegiatan'),
                'tujuan'           => Input::get('Tujuan'),
                'status'            => 'DALAM',
                'tugas'             => Input::get('Tugas'),
                'transportasi'      => Input::get('Alat'),
                'tgl_berangkat'     => date('Y-m-d',strtotime($tglmulai)),
                'tgl_hrs_kembali'   => date('Y-m-d',strtotime($tglselesai)),
                'tgl_cetak'         => date('Y-m-d'),
                'lama_perjalanan'   => Input::get('Hari_Transport'),
                'uraianRiil'        => 'Uang Transportasi',
                'nominalRiil'       => Input::get('No_Sum'),
                'keterangan_biaya'  => Input::get('Keterangan'),
                'tgl_sp'            => date('Y-m-d',strtotime(Input::get('tgl_sp'))),
                'hadir'             => Input::get('hadir'),
                'petunjuk'          => Input::get('petunjuk'),
                'masalah'           => Input::get('mslah'),
                'saran'             => Input::get('saran'),
                'lain_lain'         => Input::get('dll'),
                'ttd'               => Input::get('ttd')
            ));
            $perjalananlast = DB::table('perjalanan')->select(DB::raw('MAX(id_perjalanan) AS id_perjalanan'))->first();
            $pengikut = Input::get('Pengikut');
            for ($i=0; $i < count($pengikut) ; $i++) { 
                if($pengikut[$i] == Input::get('Tertugas')){
                    DB::table('pengikut_perjalanan')->insert(
                    array( 
                        'kode_perjalanan'       => $perjalananlast->id_perjalanan,
                        'nip_pengikut'          => $pengikut[$i],
                        'uang_harian_pengikut'  => Input::get('No_Harian_'.$i),
                        'yg_tugas'              => 1
                    ));
                }else{
                    DB::table('pengikut_perjalanan')->insert(
                    array( 
                        'kode_perjalanan'       => $perjalananlast->id_perjalanan,
                        'nip_pengikut'          => $pengikut[$i],
                        'uang_harian_pengikut'  => Input::get('No_Harian_'.$i),
                        'yg_tugas'              => 0
                    ));
                }
            }
            $textnya = preg_replace( "/\r|\n/", "",Input::get('Hasil'));
            $hasil = explode('.',$textnya);
            for ($i=0; $i < count($hasil)-1; $i++) { 
                DB::table('hasil_perjalanan')->insert(
                array( 
                    'id_perjalanan'             => $perjalananlast->id_perjalanan,
                    'uraian_hasil_perjalanan'   => substr($hasil[$i],0)
                ));
            }
            DB::table('hasil_perjalanan')->where('id_perjalanan', '=',$perjalananlast->id_perjalanan)->where('uraian_hasil_perjalanan','=','')->delete();
            Session::flash('message', 'Data Perjalanan Dinas berhasil ditambahkan');
            return Redirect::to('/dalam');
        }
    }
    public function edit($id)
    {
        $data =DB::table('perjalanan')
            ->where('id_perjalanan','=',$id)
            ->first();
        $pengikut = DB::table('pengikut_perjalanan')->where('kode_perjalanan','=',$id)->get();
        if (Auth::user()->admin==1) {
            $pegawai = DB::table('pegawai as p')->where('nip_pegawai','<>','0')->get();
            $kegiatan = DB::table('kegiatan')->get();
        } else {
            if(Auth::user()->pptk==1){
                $pegawai = DB::table('pegawai as p')
                    ->join('seksi  as s','p.id_seksi','=','s.id_seksi')
                    ->join('bidang  as b','s.id_bidang','=','b.id_bidang')
                    ->where('b.id_bidang','=',Auth::user()->id_bidang)
                    ->where('p.nip_pegawai','<>','0')->get();
                $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi  as s','k.id_seksi','=','s.id_seksi')
                    ->join('bidang  as b','s.id_bidang','=','b.id_bidang')
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->get();
            }else{
                $pegawai = DB::table('pegawai as p')
                    ->join('seksi  as s','p.id_seksi','=','s.id_seksi')
                    ->join('bidang  as b','s.id_bidang','=','b.id_bidang')
                    ->where('b.id_bidang','=',Auth::user()->id_bidang)
                    ->where('p.nip_pegawai','<>','0')->get();
                $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi  as s','k.id_seksi','=','s.id_seksi')
                    ->join('bidang  as b','s.id_bidang','=','b.id_bidang')
                    ->where('k.ppk','=',Auth::user()->nip_pegawai)
                    ->get();
            }
        }
        $ttd = DB::table('pegawai')->whereIn('id_jabatan',[2,1])->get();
        $lokasi = DB::table('desa')->get();
        $hasil =  DB::table('hasil_perjalanan')->where('id_perjalanan','=',$id)->get();
        return view('dalam.edit')->with('data',$data)->with('pengikut',$pengikut)->with('pegawai',$pegawai)->with('kegiatan',$kegiatan)->with('lokasi',$lokasi)->with('hasil',$hasil)->with('ttd',$ttd);
    }

    public function update()
    {
        $id = Input::get('idperjalanan');
        $rules = array(
            'Tertugas'          => 'required',
            'Tugas'             => 'required',
            'Tujuan'            => 'required',
            'Kegiatan'          => 'required',
            'Alat'              => 'required',
            'Tgl_Perjalanan'    => 'required',
            'tgl_sp'            => 'required',
            'ttd'               => 'required'
        );
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/dalam/'.$id.'/edit')->withErrors($validator)->withInput();
        } else { 
            $tgl= explode(" s/d ",Input::get('Tgl_Perjalanan'));
            $tglmulai = $tgl[0];
            $tglselesai = $tgl[1];
            $nom = Input::get('No_Surat');
            DB::table('perjalanan')->where('id_perjalanan',$id)->update(
                array(   
                    'nomor_perjalanan'  => '094/'.$nom.'/437.86/'.date('Y'),
                    'pegawai_tertugas'  => Input::get('Tertugas'),
                    'id_kegiatan'       => Input::get('Kegiatan'),
                    'tujuan'         => Input::get('Tujuan'),
                    'tugas'             => Input::get('Tugas'),
                    'transportasi'      => Input::get('Alat'),
                    'tgl_berangkat'     => date('Y-m-d',strtotime($tglmulai)),
                    'tgl_hrs_kembali'   => date('Y-m-d',strtotime($tglselesai)),
                    'tgl_cetak'         => date('Y-m-d'),
                    'lama_perjalanan'   => Input::get('Hari_Transport'),
                    'uraianRiil'        => 'Uang Transportasi',
                    'nominalRiil'       => Input::get('No_Sum'),
                    'keterangan_biaya'  => Input::get('Keterangan'),
                    'tgl_sp'            => date('Y-m-d',strtotime(Input::get('tgl_sp'))),
                    'hadir'             => Input::get('hadir'),
                    'petunjuk'          => Input::get('petunjuk'),
                    'masalah'           => Input::get('mslah'),
                    'saran'             => Input::get('saran'),
                    'lain_lain'         => Input::get('dll'),
                    'ttd'               => Input::get('ttd')
                )
            );
            $pengikut = DB::table('pengikut_perjalanan')->where('kode_perjalanan','=',$id)->get();
            for ($i=0; $i < count($pengikut) ; $i++) { 
                DB::table('pengikut_perjalanan')->where('id_pengikut',$pengikut[$i]->id_pengikut)->update(
                array( 
                    'kode_perjalanan'       => $id,
                    'nip_pengikut'          => $pengikut[$i]->nip_pengikut,
                    'uang_harian_pengikut'  => Input::get('No_Harian_'.$i)
                ));
            }
            DB::table('hasil_perjalanan')->where('id_perjalanan', '=',$id)->delete();
            $textnya = preg_replace( '/\r|\n/', '',Input::get('Hasil'));
            $hasil = explode('.',$textnya);
            for ($i=0; $i < count($hasil); $i++) { 
                DB::table('hasil_perjalanan')->insert(
                array( 
                    'id_perjalanan'             => $id,
                    'uraian_hasil_perjalanan'   => substr($hasil[$i],0)
                ));
            }
            DB::table('hasil_perjalanan')->where('id_perjalanan', '=',$id)->where('uraian_hasil_perjalanan','=','')->delete();
            Session::flash('message', 'Data Perjalanan Dalam Daerah berhasil diubah');
            return Redirect::to('/dalam');
        }       
    }

    public function destroy($id)
    {
        DB::table('hasil_perjalanan')->where('id_perjalanan', '=',$id)->delete();
        DB::table('pengikut_perjalanan')->where('kode_perjalanan', '=',$id)->delete();
        DB::table('perjalanan')->where('id_perjalanan', '=',$id)->delete();
        Session::flash('message', 'Data Perjalanan Dinas berhasil dihapus !');
        return Redirect::to('/dalam');
    }
    public function kekata($x) {
        $x = abs($x);
        $angka = array("", "satu", "dua", "tiga", "empat", "lima",
        "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($x <12) {
            $temp = " ". $angka[$x];
        } else if ($x <20) {
            $temp = $this->kekata($x - 10). " belas";
        } else if ($x <100) {
            $temp = $this->kekata($x/10)." puluh". $this->kekata($x % 10);
        } else if ($x <200) {
            $temp = " seratus" . $this->kekata($x - 100);
        } else if ($x <1000) {
            $temp = $this->kekata($x/100) . " ratus" . $this->kekata($x % 100);
        } else if ($x <2000) {
            $temp = " seribu" . $this->kekata($x - 1000);
        } else if ($x <1000000) {
            $temp = $this->kekata($x/1000) . " ribu" . $this->kekata($x % 1000);
        } else if ($x <1000000000) {
            $temp = $this->kekata($x/1000000) . " juta" . $this->kekata($x % 1000000);
        } else if ($x <1000000000000) {
            $temp = $this->kekata($x/1000000000) . " milyar" . $this->kekata(fmod($x,1000000000));
        } else if ($x <1000000000000000) {
            $temp = $this->kekata($x/1000000000000) . " trilyun" . $this->kekata(fmod($x,1000000000000));
        }     
            return $temp;
    }
    public function terbilang($x, $style=3) {
        if($x<0) {
            $hasil = "minus ". trim($this->kekata($x));
        } else {
            $hasil = trim($this->kekata($x));
        }     
        switch ($style) {
            case 1:
                $hasil = strtoupper($hasil);
                break;
            case 2:
                $hasil = strtolower($hasil);
                break;
            case 3:
                $hasil = ucwords($hasil);
                break;
            default:
                $hasil = ucfirst($hasil);
                break;
        }     
        return $hasil;
    }
}
