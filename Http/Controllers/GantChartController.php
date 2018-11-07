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


class GantChartController extends Controller
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
        return view('detail.gantchart')->with('data', $data);
    }
    public function showgrafik(Request $request, $id)
    {
        if ($request->ajax()) {
            $jmldetail = DB::table('detail_rencana AS d')
            ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
            ->join('jenis_rab AS jr','r.id_jenis_rab','=','jr.id_jenis_rab')
            ->join('paket AS p','jr.id_paket','=','p.id_paket')
            ->select(DB::raw('COUNT(*) AS jml'))
            ->where('p.id_paket','=',$id)  
            ->groupBy('r.pekerjaan_rab_paket')
            ->first();
            $selectnya1 ='';
            $selectnya2 ='';
            if($jmldetail!=null){
                for ($i=1; $i <= $jmldetail->jml; $i++) { 
                    if ($i==$jmldetail->jml) {
                    $selectnya1 .= 'SUM(CASE WHEN d.minggu_ke_rencana="'.$i.'" THEN d.isi_detail_rencana ELSE 0 END) AS "minggu_ke_'.$i.'"';
                    $selectnya2 .= 'SUM(CASE WHEN d.minggu_ke="'.$i.'" THEN d.isi_detail_rab ELSE 0 END) AS "minggu_ke_'.$i.'"';
                    } else {
                    $selectnya1 .= 'SUM(CASE WHEN d.minggu_ke_rencana="'.$i.'" THEN d.`isi_detail_rencana` ELSE 0 END)AS "minggu_ke_'.$i.'",';
                    $selectnya2 .= 'SUM(CASE WHEN d.minggu_ke="'.$i.'" THEN d.`isi_detail_rab` ELSE 0 END)AS "minggu_ke_'.$i.'",';
                    }
                }
            }
            $rencana = DB::table('detail_rencana AS d')
                ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab AS jr','r.id_jenis_rab','=','jr.id_jenis_rab')
                ->join('paket AS p','jr.id_paket','=','p.id_paket')
                ->join('jadwal AS j','d.minggu_ke_rencana','=','j.ke_jadwal')
                ->select('r.pekerjaan_rab_paket AS item','p.tgl_mulai','j.minggu_jadwal','j.ke_jadwal','d.isi_detail_rencana')
                ->where('p.id_paket','=',$id)
                ->orderBy('d.id_detail_rencana')
                ->get();
            $realisasi = DB::table('detail_rab AS d')
                ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab AS jr','r.id_jenis_rab','=','jr.id_jenis_rab')
                ->join('paket AS p','jr.id_paket','=','p.id_paket')
                ->join('jadwal AS j','d.minggu_ke','=','j.ke_jadwal')
                ->select('r.pekerjaan_rab_paket AS item','p.tgl_mulai','j.minggu_jadwal','j.ke_jadwal','d.isi_detail_rab')
                ->where('p.id_paket','=',$id)
                ->orderBy('d.id_detail_rab')
                ->get();
            $jadwal = DB::table('jadwal')->where('id_paket','=',$id)->get();
            $data =  array();
            $awal;
            $akhir;
            $tglakhir;
            $ke=1;
            foreach ($jadwal as $j) {
                foreach ($realisasi as $value) {
                    if ($j->minggu_jadwal==$value->minggu_jadwal) {
                        if ($ke==$value->ke_jadwal) {
                            if ($ke==1) {
                                if ($value->isi_detail_rab>0) {
                                    $data[$value->item]['item']=$value->item;
                                    $data[$value->item]['minggu_'.$ke]['start_realisasi']=$value->tgl_mulai;
                                    $data[$value->item]['minggu_'.$ke]['end_realisasi']=$value->minggu_jadwal;
                                    $data[$value->item]['minggu_'.$ke]['realisasi']=$value->isi_detail_rab;
                                }
                            } else {
                                if ($value->isi_detail_rab>0) {
                                    $data[$value->item]['item']=$value->item;
                                    $tgl1 = new Carbon($value->minggu_jadwal);
                                    $tglakhir = $tgl1->addDays(7)->toDateString();
                                    $data[$value->item]['minggu_'.$ke]['start_realisasi']=$value->minggu_jadwal;
                                    $data[$value->item]['minggu_'.$ke]['end_realisasi']=$tglakhir;
                                    $data[$value->item]['minggu_'.$ke]['realisasi']=$value->isi_detail_rab;
                                }
                            }
                        }
                    }  
                }
                foreach ($rencana as $value) {
                    if ($j->minggu_jadwal==$value->minggu_jadwal) {
                        if ($ke==$value->ke_jadwal) {
                            if ($ke==1) {
                                if ($value->isi_detail_rencana>0) {
                                    $data[$value->item]['minggu_'.$ke]['start_rencana']=$value->tgl_mulai;
                                    $data[$value->item]['minggu_'.$ke]['end_rencana']=$value->minggu_jadwal;
                                    $data[$value->item]['minggu_'.$ke]['rencana']=$value->isi_detail_rencana;
                                }
                            } else {
                                if ($value->isi_detail_rencana>0) {
                                    $tgl1 = new Carbon($value->minggu_jadwal);
                                    $tglakhir = $tgl1->addDays(7)->toDateString();
                                    $data[$value->item]['minggu_'.$ke]['start_rencana']=$value->minggu_jadwal;
                                    $data[$value->item]['minggu_'.$ke]['end_rencana']=$tglakhir;
                                    $data[$value->item]['minggu_'.$ke]['rencana']=$value->isi_detail_rencana;
                                }
                            }
                        }
                    }  
                }
                $ke+=1;
            }
            return Response::json($data);
        }
    }
    public function showjdawal(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('jadwal as j')->join('paket as p','j.id_paket','=','p.id_paket')
                ->where('p.id_paket','=',$id)->get();
            return Response::json($data);
        }
    }
}
