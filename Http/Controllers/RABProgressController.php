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


class RABProgressController extends Controller
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
        return view('detail.rabprogress')->with('data', $data);
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
                if($total<=0){
                    $prosen += 0;
                }else{
                    $prosen += ($total/$kontrak)*100;
                }
                if($totalrencana<=0){
                    $prosen1 += 0;
                }else{
                    $prosen1 += ($totalrencana/$kontrak)*100;
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
}
