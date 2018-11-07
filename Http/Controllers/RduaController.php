<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use Redirect;
use View;
use Response;
use Input;
use Auth;

class RduaController extends Controller
{
    public function index(){
        if(Auth::user()->admin==1){
            $data = DB::table('view_rdua as rdua')
                ->join('kegiatan as keg','keg.id_kegiatan','rdua.id_kegiatan')
                ->get();
        }else{
            if(Auth::user()->pptk==1){
                $data = DB::table('view_rdua as rdua')
                ->join('kegiatan as keg','keg.id_kegiatan','rdua.id_kegiatan')
                ->whereIn('keg.id_kegiatan', function($query)
                    {
                        $query->select('id_kegiatan')
                            ->from('kegiatan')
                            ->where('pptk','=',Auth::user()->nip_pegawai);
                    })
                ->get();
            }else{
                $data = DB::table('view_rdua as rdua')
                ->join('kegiatan as keg','keg.id_kegiatan','rdua.id_kegiatan')
                ->whereIn('keg.id_kegiatan', function($query)
                    {
                        $query->select('id_kegiatan')
                            ->from('kegiatan')
                            ->where('ppk','=',Auth::user()->nip_pegawai);
                    })
                ->get();
            }
        }
        return view ('rdua.rdua_list')
        ->with('data', $data);
    }

    public function update(Request $request){
        $arr=[];
        for ($i=1; $i <13 ; $i++) { 
            $arr[$i-1]=Input::get('bulan'.$i);
            if($arr[$i-1]!=null){
                DB::table('r_dua')->where('id_kegiatan',Input::get('id'))->where('bulan',$i)->update(
                    array(
                        'nilai'     => $arr[$i-1]
                    )
                );
            }
        }
        $data = DB::table('view_rdua as rdua')
        ->join('kegiatan as keg','keg.id_kegiatan','rdua.id_kegiatan')
        ->get();
        return response()->json($data);
    }
}


