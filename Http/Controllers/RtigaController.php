<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use Redirect;
use View;
use Response;
use Input;
use Auth;

class RtigaController extends Controller
{
    public function index(){
        if(Auth::user()->admin==1){
            $data = DB::table('view_rtiga as rtiga')
                ->join('kegiatan as keg','keg.id_kegiatan','rtiga.id_kegiatan')
                ->get();
        }else{
            if(Auth::user()->pptk==1){
                $data = DB::table('view_rtiga as rtiga')
                ->join('kegiatan as keg','keg.id_kegiatan','rtiga.id_kegiatan')
                ->whereIn('keg.id_kegiatan', function($query)
                    {
                        $query->select('id_kegiatan')
                            ->from('kegiatan')
                            ->where('pptk','=',Auth::user()->nip_pegawai);
                    })
                ->get();
            }else{
                $data = DB::table('view_rtiga as rtiga')
                ->join('kegiatan as keg','keg.id_kegiatan','rtiga.id_kegiatan')
                ->whereIn('keg.id_kegiatan', function($query)
                    {
                        $query->select('id_kegiatan')
                            ->from('kegiatan')
                            ->where('ppk','=',Auth::user()->nip_pegawai);
                    })
                ->get();
            }
        }
        return view ('rtiga.rtiga_list')
        ->with('data', $data);
    }
    public function update(Request $request){
        $arr=[];
        for ($i=1; $i <13 ; $i++) { 
            $arr[$i-1]=Input::get('bulan'.$i);
            if($arr[$i-1]!=null){
                DB::table('r_tiga')->where('id_kegiatan',Input::get('id'))->where('bulan',$i)->update(
                    array(
                        'nilai'     => $arr[$i-1]
                    )
                );
            }
        }
        $data = DB::table('view_rtiga as rtiga')
        ->join('kegiatan as keg','keg.id_kegiatan','rtiga.id_kegiatan')
        ->get();
        return response()->json($data);
    }
    public function cetaknya(){
        $data = DB::table('view_rtiga as rtiga')
                ->join('kegiatan as keg','keg.id_kegiatan','rtiga.id_kegiatan')
                ->join('view_rdua as rdua','keg.id_kegiatan','rdua.id_kegiatan')
                ->select('keg.nama_kegiatan','rtiga.Januari as rtjan','rdua.Januari as rdjan','rtiga.Februari as rtfeb','rdua.Februari as rdfeb','rtiga.Maret as rtmar','rdua.Maret as rdmar','rtiga.APril as rtapr','rdua.APril as rdapr','rtiga.Mei as rtmei','rdua.Mei as rdmei','rtiga.Juni as rtjun','rdua.Juni as rdjun','rtiga.Juli as rtjul','rdua.Juli as rdjul','rtiga.Agustus as rtagus','rdua.Agustus as rdagus','rtiga.September as rtsep','rdua.September as rdsep','rtiga.Oktober as rtokt','rdua.Oktober as rdokt','rtiga.November as rtnov','rdua.November as rdnov','rtiga.Desember as rtdes','rdua.Desember as rddes','rtiga.sisa as rtsis','rdua.sisa as rdsis')
                ->get();
        return view ('rtiga.cetaknya')->with('data', $data);
    }
}
