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


class OutlineController extends Controller
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
        $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
        $outline = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
        return view('detail.outline')->with('data', $data)->with('rabpaket',$rabpaket)->with('outline',$outline);
    }
    public function showrab(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('rab_paket as r')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('j.id_paket','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    //struktur
    public function showdatastruktur(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('o.tipe_outline','=',0)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showstruktur(Request $request, $id)
    {
        if ($request->ajax()) {
            $outline = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('o.tipe_outline','=',0)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelstruktur')->with('outline',$outline)->with('rabpaket',$rabpaket); 
        }
    }
    public function simpanstruktur()
    {
        DB::table('outline')->insert(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 0
            )
        );
    }
    public function ubahstruktur()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idout'))->update(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 0
            )
        );
    }
    //arsitektur
    public function showdataarsitektur(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('o.tipe_outline','=',1)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showarsitektur(Request $request, $id)
    {
        if ($request->ajax()) {
            $outline = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('o.tipe_outline','=',1)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelarsitektur')->with('outline',$outline)->with('rabpaket',$rabpaket); 
        }
    }
    public function simpanarsitektur()
    {
        DB::table('outline')->insert(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 1
            )
        );
    }
    public function ubaharsitektur()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idout'))->update(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 1
            )
        );
    }
    //mekanikal
    public function showdatamekanikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('o.tipe_outline','=',2)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showmekanikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $outline = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('o.tipe_outline','=',2)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelmekanikal')->with('outline',$outline)->with('rabpaket',$rabpaket); 
        }
    }
    public function simpanmekanikal()
    {
        DB::table('outline')->insert(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 2
            )
        );
    }
    public function ubahmekanikal()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idout'))->update(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 2
            )
        );
    }
    //elektrikal
    public function showdataelektrikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('o.tipe_outline','=',3)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showelektrikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $outline = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('o.tipe_outline','=',3)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelelektrikal')->with('outline',$outline)->with('rabpaket',$rabpaket); 
        }
    }
    public function simpanelektrikal()
    {
        DB::table('outline')->insert(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 3
            )
        );
    }
    public function ubahelektrikal()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idout'))->update(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 3
            )
        );
    }
    //plumbing
    public function showdataplumbing(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('o.tipe_outline','=',4)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showplumbing(Request $request, $id)
    {
        if ($request->ajax()) {
            $outline = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('o.tipe_outline','=',4)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelplumbing')->with('outline',$outline)->with('rabpaket',$rabpaket); 
        }
    }
    public function simpanplumbing()
    {
        DB::table('outline')->insert(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 4
            )
        );
    }
    public function ubahplumbing()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idout'))->update(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 4
            )
        );
    }
    //lain2x
    public function showdatalain(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('o.tipe_outline','=',5)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showlain(Request $request, $id)
    {
        if ($request->ajax()) {
            $outline = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('o.tipe_outline','=',5)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabellain')->with('outline',$outline)->with('rabpaket',$rabpaket); 
        }
    }
    public function simpanlain()
    {
        DB::table('outline')->insert(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 5
            )
        );
    }
    public function ubahlain()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idout'))->update(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'material_outline'         => Input::get('material'),
                'hasil_uji'                => Input::get('hasil'),
                'ciri_cacat'               => Input::get('cacat'),
                'saran_cara_perbaikan'     => Input::get('saran'),
                'tipe_outline'             => 5
            )
        );
    }
}
