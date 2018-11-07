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

class ATKController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');
	}
	public function index()
	{
        if (Auth::user()->admin==1) {
            $kegiatan = DB::table('dpa as d')
                ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                ->where('d.paket','=',4)
                ->get();
            $data = DB::table('cetak_atk as c')
                ->join('dpa as d','c.id_dpa','=','d.id_dpa')
                ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                ->get();
        } else {
            if(Auth::user()->pptk==1){
                $kegiatan = DB::table('dpa as d')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('d.paket','=',4)
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->get();
                $data = DB::table('cetak_atk as c')
                    ->join('dpa as d','c.id_dpa','=','d.id_dpa')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                    ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->get();
            }else{
                $kegiatan = DB::table('dpa as d')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('d.paket','=',4)
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)->get();
                $data = DB::table('cetak_atk as c')
                    ->join('dpa as d','c.id_dpa','=','d.id_dpa')
                    ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
                    ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                    ->join('seksi as s','p.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',Auth::user()->id_bidang)
                    ->get();
            }
        }
        $bulan=DB::table('bulan')->get();
		return view('atk.index')->with('data',$data)->with('kegiatan',$kegiatan)->with('bulan',$bulan);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('cetak_atk')
                ->where('id_atk','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function cetak($id)
    {
        $data =DB::table('cetak_atk as c')
            ->join('dpa as d','c.id_dpa','=','d.id_dpa')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->where('c.id_atk','=',$id)
            ->first();
        $ppk = DB::table('cetak_atk as c')
            ->join('dpa as d','c.id_dpa','=','d.id_dpa')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
            ->join('bidang as b','s.id_bidang','=','b.id_bidang')
            ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
            ->where('c.id_atk','=',$id)
            ->first();
        $bendahara = DB::table('pegawai as p')
            ->where('bendahara','=',1)
            ->first();
        $bilang = $this->terbilang($data->nilai_atk);
        $view = View::make('atk.cetak', array('data' => $data, 'bilang' => $bilang, 'ppk' => $ppk,'bendahara' => $bendahara, 'i' => 0))->render(); 
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
            'Tanggal_ATK'   => 'required'
        );
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()){   
            return Redirect::to('/atk')->withErrors($validator)->withInput();
        }
        else{
            DB::table('cetak_atk')->insert(
            array( 
                'uraian_atk'    => Input::get('Untuk'),
                'nilai_atk'     => Input::get('Jml'),
                'tanggal_atk'   => date('Y-m-d',strtotime(Input::get('Tanggal_ATK'))),
                'id_dpa'        => Input::get('Kegiatan'),
                'sts_kendali'   => 0
            ));
            Session::flash('message', 'Data Kiwtansi ATK berhasil ditambahkan');
            return Redirect::to('/atk');
        }
	}
    public function update()
    {
        $rules = array(
            'Jumlah_Ubah'       => 'required',
            'Untuk_Ubah'        => 'required',
            'Kegiatan_Ubah'     => 'required',
            'Tanggal_ATK_Ubah'  => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/atk')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idatk');
            DB::table('cetak_atk')->where('id_atk',$id)->update(
                array(   
                    'uraian_atk'        => Input::get('Untuk_Ubah'),
                    'nilai_atk'         => Input::get('Jml_Ubah'),
                    'tanggal_atk'       => date('Y-m-d',strtotime(Input::get('Tanggal_ATK_Ubah'))),
                    'id_dpa'            => Input::get('Kegiatan_Ubah'),
                    'sts_kendali'       => 0
                )
            );
            Session::flash('message', 'Data Kwitansi ATK  berhasil diubah');
            return Redirect::to('/atk');
        }       
    }
	public function destroy($id)
	{
		DB::table('cetak_atk')->where('id_atk', '=',$id)->delete();
        Session::flash('message', 'Data Kiwtansi ATK berhasil dihapus !');
        return Redirect::to('/atk');
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
