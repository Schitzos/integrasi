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


class StatusController extends Controller
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
        $data = DB::table('paket as p')
            ->join('dpa as a','p.id_dpa','=','a.id_dpa')
            ->join('kegiatan as k','a.id_kegiatan','=','k.id_kegiatan')
            ->join('pegawai as p1','k.ppk','=','p1.nip_pegawai')
            ->join('pegawai as p2','k.pptk','=','p2.nip_pegawai')
            ->join('pegawai as p3','p.kordinator_paket','=','p3.nip_pegawai')
            ->join('program as g','k.id_program','=','g.id_program')
            ->leftjoin('desa as d','p.id_desa','=','d.id_desa')
            ->leftjoin('kecamatan as c','d.id_kecamatan','=','c.id_kecamatan')
            ->leftjoin('kontraktor as r','p.id_kontraktor','=','r.id_kontraktor')
            ->leftjoin('konsultan as s','p.id_konsultan','=','s.id_konsultan')
            ->select('p.*','k.*','p1.nip_pegawai as nip_ppk','p1.nama_pegawai as nama_ppk','p2.nip_pegawai as nip_pptk','p2.nama_pegawai as nama_pptk','p3.nama_pegawai as nama_kordinator','g.*','c.*','d.*','r.*','s.*')
            ->where('p.id_paket','=',$id)
            ->first();
        if (Auth::user()->tipe==2) {
            $konsultan = $data->id_konsultan;
            $adminnya = DB::table('admin')->where('id_kontraktor','=',Auth::user()->id_kontraktor)->first();
            if ($data->id_konsultan!=$adminnya->lawan_lawas) {
                DB::table('admin')->where('id',Auth::user()->id)->update(array('status_pass' => 0));
            }else {
                DB::table('admin')->where('id',Auth::user()->id)->update(array('status_pass' => 1));
            }
        } elseif (Auth::user()->tipe==3) {
            $kontraktor = $data->id_kontraktor;
            $adminnya = DB::table('admin')->where('id_konsultan','=',Auth::user()->id_konsultan)->first();
            if ($data->id_kontraktor!=$adminnya->lawan_lawas) {
                DB::table('admin')->where('id',Auth::user()->id)->update(array('status_pass' => 0));
            }else {
                DB::table('admin')->where('id',Auth::user()->id)->update(array('status_pass' => 1));
            }
        } 
        $usernya = DB::table('pegawai')->where('id','=',Auth::user()->id)->first();
        $adendum = DB::table('adendum')->where('id_paket','=',$id)->get();
        $jmlpphp = DB::table('pphp')->select(DB::raw('COUNT(*) AS jml'))->where('id_paket','=',$id)->first();
        if ($jmlpphp->jml>=2) {
            $angpphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')
                ->where('p.id_paket','=',$id)->where('p.status_pphp','=',3)
                ->get();
            $ketupphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')
                ->where('p.id_paket','=',$id)->where('p.status_pphp','=',1)
                ->first();
            $sekpphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')
                ->where('p.id_paket','=',$id)->where('p.status_pphp','=',2)->first();
            return view('detail.status')->with('data', $data)->with('adendum', $adendum)->with('usernya', $usernya)->with('jmlpphp',$jmlpphp)
            ->with('angpphp',$angpphp)->with('ketupphp',$ketupphp)->with('sekpphp',$sekpphp);
        } else {
            $angpphp = null;
            $ketupphp = null;
            $sekpphp = null;
            $pphp = DB::table('pphp as p')
                ->join('pegawai as g','p.nip_pegawai','=','g.nip_pegawai')
                ->where('p.id_paket','=',$id)->where('p.status_pphp','=',0)->first();
            return view('detail.status')->with('data', $data)->with('adendum', $adendum)->with('usernya', $usernya)
            ->with('jmlpphp',$jmlpphp)->with('pphp',$pphp)->with('angpphp',$angpphp)->with('ketupphp',$ketupphp)->with('sekpphp',$sekpphp);
        }
        return view('detail.status')->with('data', $data)->with('adendum', $adendum)->with('usernya', $usernya);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('jadwal')
                ->where('id_paket','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showadendum(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('adendum')
                ->select(DB::raw('COUNT(id_paket) AS jml'))
                ->where('id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function simpanadendum()
    {
        $id = Input::get('idpaket1');
        $rules = array(
            'Nomor_Kontrak' => 'required',
            'Tgl_Adendum'   => 'required',
            'Tgl_ST1'       => 'required',
            'Tgl_ST2'       => 'required',
            'Nilai_Kontrak' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/status/'.$id)->withErrors($validator)->withInput();
        }else 
        {
            DB::table('adendum')->insert(
                array(   
                    'id_paket'                 => $id,
                    'no_kontrak_adendum'        => Input::get('Nomor_Kontrak'),
                    'tgl_kontrak_adendum'       => date('Y-m-d',strtotime(Input::get('Tgl_Adendum'))),
                    'nilai_kontrak_adendum'     => Input::get('Nilai_Kon'),
                    'tgl_st1_adendum'           => date('Y-m-d',strtotime(Input::get('Tgl_ST1'))),
                    'tgl_st2_adendum'           => date('Y-m-d',strtotime(Input::get('Tgl_ST2')))
                ));
        Session::flash('message', 'Data Adendum berhasil ditambahkan');
        return Redirect::to('/status/'.$id);
        }
    }

    public function destroy($id)
    {
        $idpro = DB::table('adendum')->where('id_adendum','=',$id)->first();
        DB::table('adendum')->where('id_adendum', '=',$id)->delete();
        Session::flash('message', 'Data Adendum berhasil dihapus !');
        return Redirect::to('/status/'.$idpro->id_paket);
    }

    public function bukakunci()
    {
        $id = Input::get('idpaket2');
        $tipenya = Input::get('tipenya');
        $usernya = Input::get('idnya');
        $rules = array(
            'Passnya' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/status/'.$id)->withErrors($validator)->withInput();
        }else 
        {
            if ($tipenya==5) {
                $paket = DB::table('paket')->where('id_paket','=',$id)->first();
                $konsultan = $paket->id_konsultan;
                $konsultannya = DB::table('admin')->where('id_konsultan','=',$konsultan)->first();
                if ($konsultannya->andropass==MD5(Input::get('Passnya'))) {
                    DB::table('admin')->where('id','=',$usernya)->update(
                    array(   
                        'status_pass'   => 1,
                        'lawan_lawas'   => $konsultan
                    ));
                    Session::flash('message', 'Kunci Ubah Data Proyek Berhasil Dibuka');
                    return Redirect::to('/status/'.$id);
                } else {
                    Session::flash('eror', 'Password Konsultan yang Anda masukkan salah!!!');
                    return Redirect::to('/status/'.$id);
                }
            }else{
                $paket = DB::table('paket')->where('id_paket','=',$id)->first();
                $kontraktor = $paket->id_kontraktor;
                $kontraktornya = DB::table('admin')->where('id_kontraktor','=',$kontraktor)->first();
                if ($kontraktornya->andropass==MD5(Input::get('Passnya'))) {
                    DB::table('admin')->where('id','=',$usernya)->update(
                    array(   
                        'status_pass'   => 1,
                        'lawan_lawas'   => $kontraktor
                    ));
                    Session::flash('message', 'Kunci Ubah Data Proyek Berhasil Dibuka');
                    return Redirect::to('/status/'.$id);
                } else {
                    Session::flash('eror', 'Password Kontraktor yang Anda masukkan salah!!!');
                    return Redirect::to('/status/'.$id);
                }
            }
        }
    }
}
