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


class PesanController extends Controller
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
    public function index()
    {   
        if (Auth::user()->tipe==5) {
            $pesan = DB::table('pesan as p')
            ->join('proyek as pr','p.id_paket','pr.id_proyek')
            ->join('admin as a','a.nama','p.id_pengirim')
            ->join('kontraktor as k','k.id_kontraktor','pr.id_kontraktor')
            ->where('pr.id_kontraktor',Auth::user()->id_kontraktor)
            ->where('p.id_admin','=',Auth::user()->tipe)
            ->get();
        }elseif (Auth::user()->tipe==6) {
            $pesan = DB::table('pesan as p')
            ->join('proyek as pr','p.id_paket','pr.id_proyek')
            ->join('admin as a','a.nama','p.id_pengirim')
            ->join('kontraktor as k','k.id_kontraktor','pr.id_kontraktor')
            ->where('pr.id_konsultan',Auth::user()->id_konsultan)
            ->where('p.id_admin','=',Auth::user()->tipe)
            ->get();
        }else{
           $pesan = DB::table('pesan as p')
            ->join('proyek as pr','p.id_paket','pr.id_proyek')
            ->join('admin as a','a.nama','p.id_pengirim')
            ->join('kontraktor as k','k.id_kontraktor','pr.id_kontraktor')
            ->where('p.id_admin','=',Auth::user()->tipe)
            ->get(); 
        }
        
        $paket = DB::table('proyek')->get();
        return view('setting.pesan')->with('paket', $paket)->with('pesan', $pesan);
    }
    public function master()
    {
        $data = DB::table('proyek')->first();
        return view('setting.pesanmaster')->with('data', $data);
    }

    public function store()
    {
        if (Input::get('admin')==56) {
            DB::table('pesan')->insert(
            array( 
                'id_paket'      => Input::get('idproyek'),
                'id_admin'      => 5,
                'judul'         => Input::get('judul'),
                'isi'           => Input::get('isi'),
                'id_pengirim'   => Auth::user()->nama
            ));
            DB::table('pesan')->insert(
            array( 
                'id_paket'      => Input::get('idproyek'),
                'id_admin'      => 6,
                'judul'         => Input::get('judul'),
                'isi'           => Input::get('isi'),
                'id_pengirim'   => Auth::user()->nama
            ));
        }elseif (Input::get('admin')==15) {
            DB::table('pesan')->insert(
            array( 
                'id_paket'      => Input::get('idproyek'),
                'id_admin'      => 5,
                'judul'         => Input::get('judul'),
                'isi'           => Input::get('isi'),
                'id_pengirim'   => Auth::user()->nama
            ));
            DB::table('pesan')->insert(
            array( 
                'id_paket'      => Input::get('idproyek'),
                'id_admin'      => 1,
                'judul'         => Input::get('judul'),
                'isi'           => Input::get('isi'),
                'id_pengirim'   => Auth::user()->nama
            ));
        }elseif (Input::get('admin')==16) {
            DB::table('pesan')->insert(
            array( 
                'id_paket'      => Input::get('idproyek'),
                'id_admin'      => 1,
                'judul'         => Input::get('judul'),
                'isi'           => Input::get('isi'),
                'id_pengirim'   => Auth::user()->nama
            ));
            DB::table('pesan')->insert(
            array( 
                'id_paket'      => Input::get('idproyek'),
                'id_admin'      => 6,
                'judul'         => Input::get('judul'),
                'isi'           => Input::get('isi'),
                'id_pengirim'   => Auth::user()->nama
            ));
        }else{
        DB::table('pesan')->insert(
            array( 
                'id_paket'      => Input::get('idproyek'),
                'id_admin'      => Input::get('admin'),
                'judul'         => Input::get('judul'),
                'isi'           => Input::get('isi'),
                'id_pengirim'   => Auth::user()->nama
            ));    
        }
        
        
        return Redirect::to('/pesan');
    }

    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('jadwal')
                ->where('id_proyek','=',$id)
                ->get();
            return Response::json($data);
        }
    }
}
