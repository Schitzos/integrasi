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
use App;
use Auth;
use Response;
use Carbon\Carbon;
use \PDF;

class SerahPertamaController extends Controller
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
    public function index($id)
    {
        $data = DB::table('paket')
            ->where('id_paket','=',$id)
            ->first();
        return view('detail.serahpertama')->with('data', $data);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('kedatangan as k')
                ->join('rab_paket as r','k.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function filterlaporan()
    {
        if(Input::get('filtertgl')) {
            $idpaket = Input::get('idpaket');
            $rules = array(
                'Tgl_Serah_Pertama_1' => 'required',
                'Tgl_Serah_Pertama_2' => 'required',
                'Tgl_Serah_Pertama_3' => 'required',
                'Tgl_Serah_Pertama_4' => 'required',
                'Tgl_Serah_Pertama_5' => 'required',
                'Tgl_Serah_Pertama_6' => 'required',
                'Nomor_Serah_Terima_1' => 'required',
                'Nomor_Serah_Terima_2' => 'required',
                'Nomor_Serah_Terima_3' => 'required',
                'Nomor_Serah_Terima_4' => 'required'
            );
            $messages = array(
                'required'  => 'Kolom :attribute harus di isi.'
                );
            $validator = Validator::make(Input::all(), $rules,$messages);
            if ($validator->fails()){
                return Redirect::to('pertama/'.$idpaket)->withErrors($validator)->withInput();
            }else {
                $tgl1 = Input::get('Tgl_Serah_Pertama_1');                
                $tgl2 = Input::get('Tgl_Serah_Pertama_2');                
                $tgl3 = Input::get('Tgl_Serah_Pertama_3');                
                $tgl4 = Input::get('Tgl_Serah_Pertama_4');
                $tgl5 = Input::get('Tgl_Serah_Pertama_5');
                $tgl6 = Input::get('Tgl_Serah_Pertama_6');
                $nomor1 = Input::get('Nomor_Serah_Terima_1');
                $nomor2 = Input::get('Nomor_Serah_Terima_2');
                $nomor3 = Input::get('Nomor_Serah_Terima_3');
                $nomor4 = Input::get('Nomor_Serah_Terima_4');
                return Redirect::to('pertama/'.$idpaket.'/'.$tgl1.'/'.$tgl2.'/'.$tgl3.'/'.$tgl4.'/'.$tgl5.'/'.$tgl6.'/'.$nomor1.'/'.$nomor2.'/'.$nomor3.'/'.$nomor4.'/'.'cetak');
            }
        }
    }
    public function cetak($id,$tgl1,$tgl2,$tgl3,$tgl4,$tgl5,$tgl6,$nomor1,$nomor2,$nomor3,$nomor4)
    {
        $data = DB::table('paket as p')
        ->join('dpa as a','p.id_dpa','=','a.id_dpa')
        ->join('kegiatan as k','a.id_kegiatan','=','k.id_kegiatan')
        ->join('program as r','k.id_program','=','r.id_program')
        ->leftjoin('kontraktor as t','p.id_kontraktor','=','t.id_kontraktor')
        ->leftjoin('konsultan as s','p.id_konsultan','=','s.id_konsultan')
        ->leftjoin('desa as d','p.id_desa','=','d.id_desa')
        ->join('kecamatan as c','d.id_kecamatan','=','c.id_kecamatan')
        ->join('pegawai as p1','k.pptk','=','p1.nip_pegawai')
        ->join('pegawai as p2','k.ppk','=','p2.nip_pegawai')
        ->join('bidang as b','p2.id_bidang','=','b.id_bidang')
        ->join('golongan as g','p2.id_golongan','=','g.id_golongan')
        ->leftjoin('hasil_bahp as h','p.id_paket','=','h.id_paket')
        ->select('p.*','k.*','r.*','t.*','s.*','d.*','c.*','b.*','g.*','h.*','p1.nip_pegawai as nip_pptk','p1.nama_pegawai as nama_pptk',
            'p2.nip_pegawai as nip_ppk','p2.nama_pegawai as nama_ppk')
        ->where('p.id_paket','=',$id)->first();
        $kepala = DB::table('pegawai')->where('id_jabatan','=',8)->first();
        $jmlpphp = DB::table('pphp')
             ->select(DB::raw('COUNT(*) AS jml'))
             ->where('id_paket','=',$id)->first();
        if ($jmlpphp->jml >1) {
            $anggotapphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')
                ->where('p.id_paket','=',$id)
                ->where('status_pphp','=',3)
                ->get();
            $ketuapphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')
                ->where('p.id_paket','=',$id)
                ->where('status_pphp','=',1)
                ->first();
            $sekpphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')
                ->where('p.id_paket','=',$id)
                ->where('status_pphp','=',2)
                ->first();
        } else {
            $anggotapphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')->where('p.id_paket','=',$id)
                ->first();
            $ketuapphp = null;
            $sekpphp = null;
        }
        $rab = DB::table('detail_rab AS d')
            ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
            ->join('jenis_rab AS j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->select('j.nama_jenis_rab AS item',DB::raw('SUM(d.isi_detail_rab * r.harga_rab_paket) AS realisasi'))
            ->where('j.id_paket','=',$id)
            ->groupBy('j.nama_jenis_rab')
            ->get();
        $total = DB::table('rab_paket AS r')
            ->join('jenis_rab AS j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->select('j.nama_jenis_rab AS item',DB::raw('SUM(r.volume_rab_paket * r.harga_rab_paket) AS total'))
            ->where('j.id_paket','=',$id)
            ->groupBy('j.nama_jenis_rab')
            ->get();
        $jenis = array();
        $jenisnya = array();
        $i=0;
        $jumlah =0;
        foreach ($rab as $r) {
            foreach ($total as $t) {
                if ($t->item==$r->item) {
                    $jenis[$i]=($r->realisasi/$t->total)*100;
                }
            }
            $jenisnya[]=$r->item;
            $i++;
            $jumlah=$i;
        }
        $hari2 = date('l',strtotime($tgl2));
        switch ($hari2) {
            case 'Sunday':
                $hari2 = "Minggu";
                break;
            case 'Monday':
                $hari2 = "Senin";
                break;
            case 'Tuesday':
                $hari2 = "Selasa";
                break;
            case 'Wednesday':
                $hari2 = "Rabu";
                break;
            case 'Thursday':
                $hari2 = "Kamis";
                break;
            case 'Friday':
                $hari2 = "Jumat";
                break;
            case 'Saturday':
                $hari2 = "Sabtu";
                break;
            default:
        }
        $hari3 = date('l',strtotime($tgl3));
        switch ($hari3) {
            case 'Sunday':
                $hari3 = "Minggu";
                break;
            case 'Monday':
                $hari3 = "Senin";
                break;
            case 'Tuesday':
                $hari3 = "Selasa";
                break;
            case 'Wednesday':
                $hari3 = "Rabu";
                break;
            case 'Thursday':
                $hari3 = "Kamis";
                break;
            case 'Friday':
                $hari3 = "Jumat";
                break;
            case 'Saturday':
                $hari3 = "Sabtu";
                break;
            default:
        }
        $tglnya2 = date('d',strtotime($tgl2));
        $tglnya2 = $this->terbilang($tglnya2);
        $bulan2 = date('m',strtotime($tgl2));;
        switch ($bulan2) {
            case 1:
                $bulan2 = "Januari";
                break;
            case 2:
                $bulan2 = "Februari";
                break;
            case 3:
                $bulan2 = "Maret";
                break;
            case 4:
                $bulan2 = "April";
                break;
            case 5:
                $bulan2 = "Mei";
                break;
            case 6:
                $bulan2 = "Juni";
                break;
            case 7:
                $bulan2 = "Juli";
                break;
            case 8:
                $bulan2 = "Agustus";
                break;
            case 9:
                $bulan2 = "September";
                break;
            case 10:
                $bulan2 = "Oktober";
                break;
            case 11:
                $bulan2 = "November";
                break;
            case 12:
                $bulan2 = "Desember";
                break;
            default:
        }
        $tglnya3 = date('d',strtotime($tgl3));
        $tglnya3 = $this->terbilang($tglnya3);
        $bulan3 = date('m',strtotime($tgl3));;
        switch ($bulan3) {
            case 1:
                $bulan3 = "Januari";
                break;
            case 2:
                $bulan3 = "Februari";
                break;
            case 3:
                $bulan3 = "Maret";
                break;
            case 4:
                $bulan3 = "April";
                break;
            case 5:
                $bulan3 = "Mei";
                break;
            case 6:
                $bulan3 = "Juni";
                break;
            case 7:
                $bulan3 = "Juli";
                break;
            case 8:
                $bulan3 = "Agustus";
                break;
            case 9:
                $bulan3 = "September";
                break;
            case 10:
                $bulan3 = "Oktober";
                break;
            case 11:
                $bulan3 = "November";
                break;
            case 12:
                $bulan3 = "Desember";
                break;
            default:
        }
        $tahun2 = date('Y',strtotime($tgl2));
        $tahun2 = $this->terbilang($tahun2);
        $tahun3 = date('Y',strtotime($tgl3));
        $tahun3 = $this->terbilang($tahun3);
        $bilangnya = $this->terbilang($data->nilai_paket);
        $view = View::make('detail.pertamapdf', array('data' => $data, 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'tgl3' => $tgl3, 
            'tgl4' => $tgl4, 'tgl5' => $tgl5, 'tgl6' => $tgl6, 'nomor1' => $nomor1, 'nomor2' => $nomor2, 'nomor3' => $nomor3,
            'nomor4' => $nomor4, 'hari2' => $hari2, 'hari3' => $hari3, 'tglnya2' => $tglnya2, 'tglnya3' => $tglnya3, 'bulan2' => $bulan2, 'bulan3' => $bulan3, 
            'tahun2' => $tahun2, 'tahun3' => $tahun3, 'jenis' => $jenis, 'jenisnya' => $jenisnya, 'jumlah' => $jumlah, 'kepala' => $kepala , 
            'bilangnya' => $bilangnya, 'jmlpphp' => $jmlpphp , 'anggotapphp' => $anggotapphp , 'ketuapphp' => $ketuapphp , 'sekpphp' => $sekpphp,  'i' => 0))->render(); 
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper([0, 0, 609.449, 935.433], 'potrait');
        $pdf->loadHTML($view);
        return $pdf->stream();
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
                $hasil = ucwords($hasil);
                break;
            case 2:
                $hasil = ucwords($hasil);
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

    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/pertama/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='pdf'||$extension=='PDF') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/laporan/SERAH_TERIMA_PERTAMAN_'.$idpaket.'.pdf';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/laporan/SERAH_TERIMA_PERTAMAN_'.$idpaket.'.PDF';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/laporan/SERAH_TERIMA_PERTAMAN_'.$idpaket.'.'.$extension,File::get($file));
                Session::flash('message', 'Data FIle Serah Terima Pertama berhasil ditambahkan');
                return Redirect::to('/pertama/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe pdf atau excel (*.pdf,*xls,*xlsx) !!');
                return Redirect::to('/pertama/'.$idpaket);
            }
        }
    }
    
    public function downloadfile($id)
    {
        $file= public_path(). "/images/dokumentasi/".$id."/laporan/SERAH_TERIMA_PERTAMAN_".$id.'.pdf';
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, "SERAH_TERIMA_PERTAMAN_".$id.'.pdf', $headers);
        
    }
}
