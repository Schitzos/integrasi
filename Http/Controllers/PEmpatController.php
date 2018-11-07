<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use Redirect;
use View;
use Response;

class PEmpatController extends Controller
{
    public function index($id,$ib){
        if ($id==0) {
            if ($ib==0) {
                $data =DB::table('view_pempat')->get();
                $bulane=0;
            } else {
                $data =DB::table('view_pempat')->where('bulan','=',$ib)->get();
                $bulane=$ib;
            }
            $judule = 'Semua Bidang';
            $bidange = 0;
        } else {
            if ($ib==0) {
                $data =DB::table('view_pempat')->where('id_bidang','=',$id)->get();
                $bulane=0;
            } else {
                $data =DB::table('view_pempat')->where('id_bidang','=',$id)->where('bulan','=',$ib)->get();
                $bulane=$ib;
            }
            $bidangnya = DB::table('bidang')->where('id_bidang',$id)->first();
            $judule =$bidangnya->nama_bidang;
            $bidange=$id;
        }
        $bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
        $bulan = DB::table('bulan')->get();
        return view ('pempat.pempat_list')->with('data',$data)->with('bidange',$bidange)->with('bulane',$bulane)
        ->with('bidang', $bidang)->with('bulan', $bulan)->with('judule',$judule);
    }
    public function grafik(){
        $kegiatan = DB::table('kegiatan')->get();
        return view ('pempat.grafik')->with('kegiatan',$kegiatan);
    }
    public function datar2(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('r_dua')
            ->join('bulan','bulan.kode_bulan','r_dua.bulan')
            ->where('id_kegiatan', $id)->orderBy('bulan')->get();
            return Response::json($data);
        }
    }
    public function datar3(Request $request, $id){
        if ($request->ajax()) {
            $data2 = DB::table('r_tiga')
            ->join('bulan','bulan.kode_bulan','r_tiga.bulan')
            ->where('id_kegiatan', $id)->orderBy('bulan')->get();
            return Response::json($data2);
        }
    }
}
