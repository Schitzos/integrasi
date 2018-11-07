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


class KalenderController extends Controller
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
        if ($id==0) {
            $data = DB::table('paket')->first();
            $kalender = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')->get();
        } elseif ($id==5) {
            $data = DB::table('paket')->where('id_kontraktor','=',Auth::user()->id_kontraktor)->first();
            $kalender = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')
                ->where('p.id_kontraktor','=',Auth::user()->id_kontraktor)
                ->get();
        } elseif ($id==6) {
            $data = DB::table('paket')->where('id_konsultan','=',Auth::user()->id_konsultan)->first();
            $kalender = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')
                ->where('p.id_konsultan','=',Auth::user()->id_konsultan)
                ->get();
        } 
        return view('setting.kalender')->with('data', $data)->with('kalender',$kalender);
    }
    public function master($id)
    {
        if ($id==0) {
            $data = DB::table('paket')->first();
            $kalender = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')->get();
        } elseif ($id==5) {
            $data = DB::table('paket')->where('id_kontraktor','=',Auth::user()->id_kontraktor)->first();
            $kalender = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')
                ->where('p.id_kontraktor','=',Auth::user()->id_kontraktor)
                ->get();
        } elseif ($id==6) {
            $data = DB::table('paket')->where('id_konsultan','=',Auth::user()->id_konsultan)->first();
            $kalender = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')
                ->where('p.id_konsultan','=',Auth::user()->id_konsultan)
                ->get();
        } 
        return view('setting.kalendermaster')->with('data', $data)->with('kalender',$kalender);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            if ($id==0) {
                $data = DB::table('paket')->get();
            } elseif ($id==5) {
                $data = DB::table('paket')->where('id_kontraktor','=',Auth::user()->id_kontraktor)->get();
            } elseif ($id==6) {
                $data = DB::table('paket')->where('id_konsultan','=',Auth::user()->id_konsultan)->get();
            } 
            return Response::json($data);
        }
    }
    public function showdetail(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('jadwal_rapat')->where('id_jadwal_rapat','=',$id)->first();
            return Response::json($data);
        }
    }
    public function showkalender(Request $request, $id)
    {
        if ($request->ajax()) {
            if ($id==0) {
                $data = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')->get();
            } elseif ($id==5) {
                $data = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')
                    ->where('p.id_kontraktor','=',Auth::user()->id_kontraktor)
                    ->get();
            } elseif ($id==6) {
                $data = DB::table('jadwal_rapat as j')->join('paket as p','j.id_paket','=','p.id_paket')
                    ->where('p.id_konsultan','=',Auth::user()->id_konsultan)
                    ->get();
            } 
            return Response::json($data);
        }
    }
    public function showpaket(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('paket')->where('nama_paket','=',$id)->first();
            return Response::json($data);
        }
    }
    public function store()
    {
        DB::table('jadwal_rapat')->insert(
            array(   
                'id_paket'      => Input::get('id'),
                'tgl_rapat'      => date('Y-m-d',strtotime(Input::get('tgl'))),
                'lokasi_rapat'   => Input::get('lokasi'),
                'jam_mulai'      => Input::get('mulai'),
            )
        );
    }
    public function update()
    {
        DB::table('jadwal_rapat')->where('id_jadwal_rapat','=',Input::get('id')) ->update(
            array(   
                'tgl_rapat'      => date('Y-m-d',strtotime(Input::get('tgl')))
            )
        );
    }
}
