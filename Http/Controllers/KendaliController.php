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

class KendaliController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id,$ik,$ib)
    {
        if (Auth::user()->admin==1||Auth::user()->bendahara==1) {
            if ($id==0) {
                if ($ik==0) {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('l.id_kegiatan','=',$ik)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('l.id_kegiatan','=',$ik)
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=0;
                $kegiatan = DB::table('kegiatan')->get();
            } else {
                if ($ik==0) {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->where('l.id_kegiatan','=',$ik)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->where('l.id_kegiatan','=',$ik)
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=$id;
                $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',$id)
                    ->get();
            }
        } else {
            if ($id==0) {
                if ($ik==0) {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=0;
                if(Auth::user()->ppk==1){
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('k.ppk','=',Auth::user()->nip_pegawai)
                    ->get();
                }else{
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    
                    ->get();
                }
            } else {
                if ($ik==0) {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=$id;
                if (Auth::user()->ppk==1) {
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',$id)
                    ->where('k.ppk','=',Auth::user()->nip_pegawai)
                    ->get();  
                } else {
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',$id)
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->get();  
                }
            }
        }
        $bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
        $bulan = DB::table('bulan')->get();
        return view('kendali.index')->with('data',$data)->with('bidang',$bidang)->with('kegiatan',$kegiatan)->with('bulan',$bulan)
        ->with('bidange',$bidange)->with('kegiatane',$kegiatane)->with('bulane',$bulane);
    }
    public function simpan()
    {
        $rutene = Input::get('routene');
        $idtabel = Input::get('idtabel');
        if ($rutene=='/dalam') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 0,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('perjalanan')->where('id_perjalanan',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Perjalanan Dinas berhasil diajukan');
        } elseif($rutene=='/luar') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 1,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('perjalanan')->where('id_perjalanan',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Perjalanan Dinas berhasil diajukan');
        } elseif($rutene=='/atk') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 2,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('cetak_atk')->where('id_atk',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Atk berhasil diajukan');
        } elseif($rutene=='/mamin') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 3,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('cetak_mamin')->where('id_mamin',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Mamin berhasil diajukan');
        } elseif($rutene=='/pengadaan') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 4,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('cetak_pengadaan')->where('id_pengadaan',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Penggandaan berhasil diajukan');
        } elseif($rutene=='/honor/pphp') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 5,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('honor_pphp')->where('id_pphp',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Honor PPHP berhasil diajukan');
        } elseif($rutene=='/honor/ppb') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 6,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('honor_ppb')->where('id_ppb',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Honor PPB berhasil diajukan');
        } elseif($rutene=='/honor/timteknis') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 7,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('honor_timteknis')->where('id_timteknis',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Honor TIM Teknis berhasil diajukan');
        } elseif($rutene=='/honor/bulanan') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 8,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('honor_bulanan')->where('id_bulanan',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Honor Bulanan berhasil diajukan');
        } elseif($rutene=='/honorrapat') {
            DB::table('kendali')->insert(
                array( 
                    'id_kegiatan'       => Input::get('idkegiatan'),
                    'id_tabel'          => $idtabel,
                    'tipe_spj'          => 9,
                    'status_kendali'    => 0,
                    'bulan_kendali'     => Input::get('Bulan_Kendali')
                ));
            DB::table('honor_rapat')->where('id_hr',$idtabel)->update(
                array( 
                    'sts_kendali'    => 1
                ));
            Session::flash('message', 'Data Honor Rapat berhasil diajukan');
        }
        return Redirect::to($rutene);
        
    }
    public function setuju()
    {
        $idkegiatan = Input::get('idkegiatan');
        $id = Input::get('idkendali');
        $kendaline = DB::table('kendali')->where('id_kendali',$id)->first();
        DB::table('kendali')->where('id_kendali',$id)->update(
            array(
                'status_kendali'  => 1,
                'bulan_kendali'      => Input::get('Bulan_Kendali')
            )
        );
        Session::flash('message', 'Data SPJ berhasil disetujui');
        return Redirect::to('/kendali/0/0/0');       
    }
    public function hapus($id)
    {
        $kendaline = DB::table('kendali')->where('id_kendali',$id)->first();
        DB::table('kendali')->where('id_kendali', '=',$id)->delete();
        Session::flash('message', 'Data SPJ berhasil di tolak atau dihapus !');
        return Redirect::to('/kendali/0/0/0');
    }

    public function index_bendahara($id,$ik,$ib)
    {
        if (Auth::user()->admin==1||Auth::user()->bendahara==1) {
            if ($id==0) {
                if ($ik==0) {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->whereIn('status_kendali',[1,2])
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('l.status_kendali',[1,2])
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('status_kendali',1)
                            ->where('l.id_kegiatan','=',$ik)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('l.status_kendali',1)
                            ->where('l.id_kegiatan','=',$ik)
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=0;
                $kegiatan = DB::table('kegiatan')->get();
            } else {
                if ($ik==0) {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if($ib==0){
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->where('l.id_kegiatan','=',$ik)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=0;
                    }else{
                        $data = DB::table('kendali as l')
                            ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                            ->where('s.id_bidang','=',$id)
                            ->where('l.id_kegiatan','=',$ik)
                            ->where('l.bulan_kendali','=',$ib)
                            ->orderBy('l.status_kendali','ASC')
                            ->orderBy('l.bulan_kendali','ASC')
                            ->get();
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=$id;
                $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',$id)
                    ->get();
            }
        } else {
            if ($id==0) {
                if ($ik==0) {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=0;
                if(Auth::user()->ppk==1){
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('k.ppk','=',Auth::user()->nip_pegawai)
                    ->get();
                }else{
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    
                    ->get();
                }
            } else {
                if ($ik==0) {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=0;
                } else {
                    if ($ib==0) {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=0;           
                    } else {
                        if(Auth::user()->ppk==1){
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.ppk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }else{
                            $data = DB::table('kendali as l')
                                ->join('kegiatan as k','l.id_kegiatan','=','k.id_kegiatan')
                                ->join('pegawai as p','k.pptk','=','p.nip_pegawai')
                                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                                ->where('p.nip_pegawai','=',Auth::user()->nip_pegawai)
                                ->where('l.bulan_kendali','=',$ib)
                                ->where('k.id_kegiatan','=',$ik)
                                ->where('s.id_bidang','=',$id)
                                ->orderBy('l.status_kendali','ASC')
                                ->orderBy('l.bulan_kendali','ASC')
                                ->get();
                        }
                        $bulane=$ib;
                    }
                    $kegiatane=$ik;
                }
                $bidange=$id;
                if (Auth::user()->ppk==1) {
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',$id)
                    ->where('k.ppk','=',Auth::user()->nip_pegawai)
                    ->get();  
                } else {
                    $kegiatan = DB::table('kegiatan as k')
                    ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                    ->where('s.id_bidang','=',$id)
                    ->where('k.pptk','=',Auth::user()->nip_pegawai)
                    ->get();  
                }
            }
        }
        $bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
        $bulan = DB::table('bulan')->get();
        return view('kendali.index_bendahara')->with('data',$data)->with('bidang',$bidang)->with('kegiatan',$kegiatan)->with('bulan',$bulan)
        ->with('bidange',$bidange)->with('kegiatane',$kegiatane)->with('bulane',$bulane);
    }

    public function setuju_b()
    {
        $idkegiatan = Input::get('idkegiatan');
        $id = Input::get('idkendali');
        $kendaline = DB::table('kendali')->where('id_kendali',$id)->first();
        DB::table('kendali')->where('id_kendali',$id)->update(
            array(
                'status_kendali'  => 2
            )
        );
        if($kendaline->tipe_spj==0) {
            DB::table('perjalanan')->where('id_perjalanan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('perjalanan')->where('id_perjalanan',$kendaline->id_tabel)->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',2)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->nominalRiil,
                    'oke_oce'            => 1,
                    'bulan_setuju'      =>Input::get('Bulan_Kendali')
                )
            );
        } elseif ($kendaline->tipe_spj==1) {
            DB::table('perjalanan')->where('id_perjalanan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('perjalanan')->where('id_perjalanan',$kendaline->id_tabel)->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',3)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->nominalRiil,
                    'oke_oce'            => 1,
                    'bulan_setuju'      =>Input::get('Bulan_Kendali')
                )
            );
        } elseif ($kendaline->tipe_spj==2) {
            DB::table('cetak_atk')->where('id_atk',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('cetak_atk')->where('id_atk',$kendaline->id_tabel)->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',4)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->nilai_atk,
                    'oke_oce'            => 1,
                    'bulan_setuju'      =>Input::get('Bulan_Kendali')
                )
            );
        } elseif ($kendaline->tipe_spj==3) {
            DB::table('cetak_mamin')->where('id_mamin',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('cetak_mamin')->where('id_mamin',$kendaline->id_tabel)->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',5)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->nilai_mamin,
                    'oke_oce'            => 1,
                    'bulan_setuju'      =>Input::get('Bulan_Kendali')
                )
            );
        }   elseif ($kendaline->tipe_spj==4) {
            DB::table('cetak_pengadaan')->where('id_pengadaan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('cetak_pengadaan')->where('id_pengadaan',$kendaline->id_tabel)->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',6)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->nilai_pengadaan,
                    'oke_oce'            => 1,
                    'bulan_setuju'      => Input::get('Bulan_Kendali')
                )
            );
        }  elseif ($kendaline->tipe_spj==5) {
            DB::table('honor_pphp')->where('id_pphp',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('list_h_pphp')->where('id_h_pphp',$kendaline->id_tabel)
                ->select(DB::raw('SUM(honor) AS jml'))
                ->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',7)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->jml,
                    'oke_oce'            => 1,
                    'bulan_setuju'      => Input::get('Bulan_Kendali')
                )
            );
        } elseif ($kendaline->tipe_spj==6) {
            DB::table('honor_ppb')->where('id_ppb',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('list_h_ppb')->where('id_h_ppb',$kendaline->id_tabel)
                ->select(DB::raw('SUM(honor) AS jml'))
                ->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',8)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->jml,
                    'oke_oce'            => 1,
                    'bulan_setuju'      => Input::get('Bulan_Kendali')
                )
            );
        } elseif ($kendaline->tipe_spj==7) {
            DB::table('honor_timteknis')->where('id_timteknis',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('list_h_timteknis')->where('id_h_timteknis',$kendaline->id_tabel)
                ->select(DB::raw('SUM(honor) AS jml'))
                ->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',9)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->jml,
                    'oke_oce'           => 1,
                    'bulan_setuju'      => Input::get('Bulan_Kendali')
                )
            );
        } elseif ($kendaline->tipe_spj==8) {
            DB::table('honor_bulanan')->where('id_bulanan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('list_h_bulanan')->where('id_h_bulanan',$kendaline->id_tabel)
                ->select(DB::raw('SUM(honor) AS jml'))
                ->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',10)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->jml,
                    'oke_oce'           => 1,
                    'bulan_setuju'      => Input::get('Bulan_Kendali')
                )
            );
        } elseif ($kendaline->tipe_spj==9) {
            DB::table('honor_rapat')->where('id_hr',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 2
                )
            );
            $nominale = DB::table('honor_rapat')->where('id_hr',$kendaline->id_tabel)->first();
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',11)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 100,
                    'progres_keuangan'  => $nominale->honor,
                    'oke_oce'           => 1,
                    'bulan_setuju'      => Input::get('Bulan_Kendali')
                )
            );
        } 
        Session::flash('message', 'Data SPJ berhasil disetujui');
        return Redirect::to('/kendali_bendahara/0/0/0');       
    }

    public function hapus_b($id)
    {
        $kendaline = DB::table('kendali')->where('id_kendali',$id)->first();
        DB::table('kendali')->where('id_kendali',$id)->update(
            array(   
                'status_kendali'  => 1
            )
        );
        if($kendaline->tipe_spj==0) {
            DB::table('perjalanan')->where('id_perjalanan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',2)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        } elseif ($kendaline->tipe_spj==1) {
            DB::table('perjalanan')->where('id_perjalanan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',3)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        } elseif ($kendaline->tipe_spj==2) {
            DB::table('cetak_atk')->where('id_atk',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',4)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        } elseif ($kendaline->tipe_spj==3) {
            DB::table('cetak_mamin')->where('id_mamin',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',5)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        }   elseif ($kendaline->tipe_spj==4) {
            DB::table('cetak_pengadaan')->where('id_pengadaan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',6)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        }  elseif ($kendaline->tipe_spj==5) {
            DB::table('honor_pphp')->where('id_pphp',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',7)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        } elseif ($kendaline->tipe_spj==6) {
            DB::table('honor_ppb')->where('id_ppb',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
        } elseif ($kendaline->tipe_spj==7) {
            DB::table('honor_timteknis')->where('id_timteknis',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',8)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        } elseif ($kendaline->tipe_spj==8) {
            DB::table('honor_bulanan')->where('id_bulanan',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',9)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        } elseif ($kendaline->tipe_spj==9) {
            DB::table('honor_rapat')->where('id_hr',$kendaline->id_tabel)->update(
                array(   
                    'sts_kendali'  => 3
                )
            );
            $dpane = DB::table('dpa')->where('id_kegiatan',$kendaline->id_kegiatan)->where('paket',10)->first();
            DB::table('dpa')->where('id_dpa',$dpane->id_dpa)->update(
                array(
                    'progres_fisik'     => 0,
                    'progres_keuangan'  => 0,
                    'oke_oce'           => 0,
                    'bulan_setuju'      => 0
                )
            );
        } 
        Session::flash('message', 'Data SPJ berhasil di tolak atau dihapus !');
        return Redirect::to('/kendali_bendahara/0/0/0');
    }
}
