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
use \Excel;

class KegiatanController extends Controller
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
        $data = DB::table('kegiatan as k')
            ->join('seksi as s','k.id_seksi','=','s.id_seksi')
            ->join('program as p','k.id_program','=','p.id_program')
            ->join('pegawai as ppk','k.ppk','=','ppk.nip_pegawai')
            ->join('pegawai as pptk','k.pptk','=','pptk.nip_pegawai')
            ->leftjoin('pegawai as ppbj','k.ppbj','=','ppbj.nip_pegawai')
            ->select('k.*','s.*','p.*','ppk.nama_pegawai as ppk','pptk.nama_pegawai as pptk','ppbj.nama_pegawai as ppbj')
            ->get();
        $program = DB::table('program')->get();
        $seksi = DB::table('seksi')->where('id_seksi','<>',0)->get();
        $ppbj = DB::table('pegawai')
            ->where('ppbj','=',1)
            ->get();
        $ppk = DB::table('pegawai')
            ->where('ppk','=',1)
            ->get();
        $pptk = DB::table('pegawai')
            ->where('pptk','=',1)
            ->get();
        return view('master.kegiatan')->with('data', $data)->with('seksi', $seksi)->with('program',$program)->with('ppbj',$ppbj)
            ->with('ppk',$ppk)->with('pptk',$pptk);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('kegiatan as k')
                ->join('seksi as s','k.id_seksi','=','s.id_seksi')
                ->join('program as p','k.id_program','=','p.id_program')
                ->where('k.id_kegiatan','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showprogram(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('program')
                ->where('id_program','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    

    public function showppk(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
                ->join('jabatan as j','p.id_jabatan','=','j.id_jabatan')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->join('bidang as b','p.id_bidang','=','b.id_bidang')
                ->where('p.id_bidang','=',$id)
                ->where('j.nama_jabatan','LIKE','PPK%')
                ->get();
            return Response::json($data);
        }
    }
    public function showpptk(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
                ->join('jabatan as j','p.id_jabatan','=','j.id_jabatan')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->join('bidang as b','p.id_bidang','=','b.id_bidang')
                ->where('p.id_bidang','=',$id)
                ->where('j.nama_jabatan','LIKE','PPTK%')
                ->get();
            return Response::json($data);
        }
    }
    public function store()
    {
        $rules = array(
            'Seksi'        => 'required|integer',
            'Program'       => 'required',
            'PPK'           => 'required',
            'PPTK'          => 'required',
            'id_kegiatan'   => 'required|unique:kegiatan',
            'Nama_Kegiatan' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.',
            'unique'  => 'Kolom Kode Kegiatan sudah ada, masukkan yang lain.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/kegiatan')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('kegiatan')->insert(
                array(   
                    'id_program'    => Input::get('Program'),
                    'id_seksi'      => Input::get('Seksi'),
                    'ppk'           => Input::get('PPK'),
                    'pptk'          => Input::get('PPTK'),
                    'ppbj'          => Input::get('PPBJ'),
                    'id_kegiatan'   => Input::get('id_kegiatan'),
                    'nama_kegiatan' => Input::get('Nama_Kegiatan')
                ));
        Session::flash('message', 'Data Kegiatan berhasil ditambahkan');
        return Redirect::to('/kegiatan');
        }
    }
    public function update()
    {
        $rules = array(
            'Seksi_Ubah'            => 'required|integer',
            'Program_Ubah'          => 'required',
            'PPK_Ubah'              => 'required',
            'PPTK_Ubah'             => 'required',
            'Kode_Kegiatan_Ubah'    => 'required',
            'Nama_Kegiatan_Ubah'    => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.',
            'integer'  => 'Kolom :attribute harus di pilih.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/kegiatan')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idkegiatan');
            if($id==Input::get('Kode_Kegiatan_Ubah')){
                DB::table('kegiatan')->where('id_kegiatan',$id)->update(
                    array(   
                        'id_program'    => Input::get('Program_Ubah'),
                        'id_seksi'      => Input::get('Seksi_Ubah'),
                        'ppk'           => Input::get('PPK_Ubah'),
                        'pptk'          => Input::get('PPTK_Ubah'),
                        'ppbj'          => Input::get('PPBJ_Ubah'),
                        'nama_kegiatan' => Input::get('Nama_Kegiatan_Ubah')
                    )
                );
                Session::flash('message', 'Data Kegiatan  berhasil diubah');
            }else{
                $ada = DB::table('kegiatan')->where('id_kegiatan','=',Input::get('Kode_Kegiatan_Ubah'))->count();
                if($ada!=0){
                    Session::flash('eror', 'Kode Kegiatan sudah ada, masukkan yang lain');
                }else{
                    DB::table('kegiatan')->where('id_kegiatan',$id)->update(
                        array(
                            'id_kegiatan'   => Input::get('Kode_Kegiatan_Ubah'),
                            'id_program'    => Input::get('Program_Ubah'),
                            'id_seksi'      => Input::get('Seksi_Ubah'),
                            'ppk'           => Input::get('PPK_Ubah'),
                            'pptk'          => Input::get('PPTK_Ubah'),
                            'ppbj'          => Input::get('PPBJ_Ubah'),
                            'nama_kegiatan' => Input::get('Nama_Kegiatan_Ubah')
                        )
                    );
                    Session::flash('message', 'Data Kegiatan  berhasil diubah');
                }
            }
            return Redirect::to('/kegiatan');
        }       
    }
    public function destroy($id)
    {
        $ada = DB::table('dpa')
                ->select(DB::raw('COUNT(id_kegiatan) as jml'))
                ->where('id_kegiatan','=',$id)->first();
        if ($ada->jml != 0) {
            Session::flash('eror', 'Data Kegiatan tidak dapat dihapus !, karena masih memiliki data DPA');   
        } else {
            DB::table('dpa')->where('id_kegiatan', '=',$id)->delete();
            DB::table('kegiatan')->where('id_kegiatan', '=',$id)->delete();
            Session::flash('message', 'Data Kegiatan berhasil dihapus !');
        }
        return Redirect::to('/kegiatan');
    }
    public function dpa($id)
    {
        $data = DB::table('dpa as d')
            ->where('d.id_kegiatan','=',$id)
            ->get();
        $kegiatan = DB::table('kegiatan as k')
            ->join('program as p','k.id_program','=','k.id_program')
            ->where('k.id_kegiatan','=',$id)
            ->first();
        $tipene = DB::table('tipe_paket')->get();
        return view('master.dpa')->with('data', $data)->with('tipene',$tipene)->with('kegiatan', $kegiatan);
    }
    public function uploaddpa()
    {
        $idkegiatan = Input::get('idkegiatan');
        $rules = array(
            'fileexcel1'      => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'            
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/kegiatan/dpa/'.$idkegiatan)->withErrors($validator)->withInput();
        }else 
        {
            $data = Excel::load(Input::file('fileexcel1'), function($reader) {})->get();
            if(!empty($data) && $data->count()){
                foreach ($data as $key => $value) {
                    if ($value->rekening!='' && $value->rekening!=null) {
                        $onok = DB::table('dpa as a')
                            ->join('rekening as b','a.'.$value->rekening.'=','b.nomor_rekening')
                            ->where('id_kegiatan','=',$idkegiatan)
                            ->where('rekening','=',$value->rekening)
                            ->count();                        
                        if ($onok==0) {
                            DB::table('dpa')->insert(
                                array(   
                                    'id_kegiatan'   => $idkegiatan,
                                    'rekening'      => $value->rekening,
                                    'uraian'        => $value->uraian,
                                    'volume'        => $value->volume,
                                    'nilai'         => $value->nilai,
                                    'paket'         => $value->id_tipe_paket
                                ));
                        }else{
                            $dpa= DB::table('dpa')
                                ->where('id_kegiatan','=',$idkegiatan)
                                ->where('rekening','=',$value->rekening)
                                ->first();
                            DB::table('dpa')->where('id_dpa',$dpa->id_dpa)->update(
                                array(
                                    'id_kegiatan'   => $idkegiatan,
                                    'rekening'      => $value->rekening,
                                    'uraian'        => $value->uraian,
                                    'volume'        => $value->volume,
                                    'nilai'         => $value->nilai,
                                    'paket'         => $value->id_tipe_paket
                                )
                            );
                        }     
                    }
                }
                Session::flash('message', 'Data DPA berhasil diunggah');
            }
            return Redirect::to('/kegiatan/dpa/'.$idkegiatan);
        }
    }
    public function dpapaket(Request $request)
    {
        $id  = Input::get('id');
        $paket = Input::get('paket');
        DB::table('dpa')->where('id_dpa',$id)->update(
            array(
                'paket'   => $paket
            )
        );
        $dpa= DB::table('dpa')
            ->where('id_dpa','=',$id)
            ->first();
        $sumpaket = DB::table('dpa')
            ->select(DB::raw('SUM(nilai) as total'))
            ->where('id_kegiatan','=',$dpa->id_kegiatan)
            ->where('paket','=',1)
            ->first();
        $sumnonpaket = DB::table('dpa')
            ->select(DB::raw('SUM(nilai) as total'))
            ->where('id_kegiatan','=',$dpa->id_kegiatan)
            ->where('paket','<>',1)
            ->first();
        DB::table('kegiatan')->where('id_kegiatan',$dpa->id_kegiatan)->update(
            array(
                'pagu_paket' => $sumpaket->total,
                'pagu_non_paket' => $sumnonpaket->total
            )
        );
        $data = DB::table('dpa as d')
            ->where('d.id_kegiatan','=',$dpa->id_kegiatan)
            ->get();
        return response()->json($data);
    }

    public function hapusdpa($id){
        DB::table('dpa')->where('id_kegiatan','=',$id)->delete();
        return Redirect::to('/kegiatan/dpa/'.$id);
    }
}
