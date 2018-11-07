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

class MaminController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->admin==1) {
            $kegiatan = DB::table('dpa as d')
                ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                ->where('d.paket','=',5)
                ->get();
            $data = DB::table('cetak_mamin as c')
                ->join('dpa as d','c.id_dpa','=','d.id_dpa')
                ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                ->get();
        } else {
            if(Auth::user()->pptk==1){
                $data = DB::table('cetak_mamin as c')
                    ->join('dpa as d','c.id_dpa','=','d.id_dpa')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                    ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->get();
                $kegiatan = DB::table('dpa as d')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('d.paket','=',5)
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->get();
            }else{
                $data = DB::table('cetak_mamin as c')
                    ->join('dpa as d','c.id_dpa','=','d.id_dpa')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                    ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->get();
                $kegiatan = DB::table('dpa as d')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('d.paket','=',5)
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)->get();
            }
        }
        $bulan = DB::table('bulan')->get();
        return view('mamin.index')->with('data',$data)->with('kegiatan',$kegiatan)->with('bulan',$bulan);   
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('cetak_mamin')
                ->where('id_mamin','=',$id)
                ->first();
            return Response::json($data);
        }
    }

    public function cetak($id)
    {
       $data =DB::table('cetak_mamin as c')
            ->join('dpa as d','c.id_dpa','=','d.id_dpa')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->where('c.id_mamin','=',$id)
            ->first();
        $ppk = DB::table('cetak_mamin as c')
            ->join('dpa as d','c.id_dpa','=','d.id_dpa')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
            ->join('jabatan as j','p.id_jabatan','=','j.id_jabatan')
            ->where('c.id_mamin','=',$id)
            ->first();
        $pptk = DB::table('cetak_mamin as c')
            ->join('dpa as d','c.id_dpa','=','d.id_dpa')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
            ->join('jabatan as j','p.id_jabatan','=','j.id_jabatan')
            ->where('c.id_mamin','=',$id)
            ->first();
        $bendahara = DB::table('pegawai')
            ->where('bendahara','=',1)
            ->first();
        $bilang = $this->terbilang($data->nilai_mamin);
        $view = View::make('mamin.cetak', array('data' => $data, 'bilang' => $bilang, 'ppk' => $ppk, 'pptk' => $pptk, 'bendahara' => $bendahara, 'i' => 0))->render(); 
        $pdf = App::make('dompdf.wrapper');
        $paper_orientation = 'pottrait';
        $pdf->setpaper('a4',$paper_orientation);
        $pdf->loadHTML($view);
        return $pdf->stream();
    }

    public function store()
    {
        $rules = array(
            'Jumlah'        => 'required',
            'Untuk'         => 'required',
            'Kegiatan'      => 'required',
            'Tanggal_Mamin' => 'required'
        );
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()){   
            return Redirect::to('/mamin')->withErrors($validator)->withInput();
        }
        else{
            DB::table('cetak_mamin')->insert(
            array( 
                'uraian_mamin'        => Input::get('Untuk'),
                'nilai_mamin'         => Input::get('Jml'),
                'tanggal_mamin'       => date('Y-m-d',strtotime(Input::get('Tanggal_Mamin'))),
                'id_dpa'              => Input::get('Kegiatan'),
                'sts_kendali'         => 0
            ));
            Session::flash('message', 'Data Kiwtansi Mamin berhasil ditambahkan');
            return Redirect::to('/mamin');
        }
    }

    public function update()
    {
        $rules = array(
            'Jumlah_Ubah'           => 'required',
            'Untuk_Ubah'            => 'required',
            'Kegiatan_Ubah'         => 'required',
            'Tanggal_Mamin_Ubah'    => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/mamin')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idmamin');
            DB::table('cetak_mamin')->where('id_mamin',$id)->update(
                array(   
                    'uraian_mamin'  => Input::get('Untuk_Ubah'),
                    'nilai_mamin'   => Input::get('Jml_Ubah'),
                    'tanggal_mamin' => date('Y-m-d',strtotime(Input::get('Tanggal_Mamin_Ubah'))),
                    'id_dpa'        => Input::get('Kegiatan_Ubah'),
                    'sts_kendali'   => 0
                )
            );
            Session::flash('message', 'Data Kwitansi Mamin  berhasil diubah');
            return Redirect::to('/mamin');
        }       
    }

    public function destroy($id)
    {
        DB::table('cetak_mamin')->where('id_mamin', '=',$id)->delete();
        Session::flash('message', 'Data Kiwtansi Mamin berhasil dihapus !');
        return Redirect::to('/mamin');
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