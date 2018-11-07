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

class JadwalKedatanganController extends Controller
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
        $kedatangan = DB::table('kedatangan as k')
            ->join('rab_paket as r','k.id_rab_paket','=','r.id_rab_paket')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
        return view('detail.kedatangan')->with('data', $data)->with('kedatangan',$kedatangan)->with('rabpaket',$rabpaket);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('kedatangan as k')
                ->join('rab_paket as r','k.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
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
    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $idked = Input::get('idjaked');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/jaked/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='pdf'||$extension=='PDF') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/laporan/KEDATANGAN_'.$idpaket.'_'.$idked.'.pdf';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/laporan/KEDATANGAN_'.$idpaket.'_'.$idked.'.PDF';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/laporan/KEDATANGAN_'.$idpaket.'_'.$idked.'.'.$extension,File::get($file));
                Session::flash('message', 'Data Laporan Harian berhasil diunggah');
                return Redirect::to('/jaked/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/jaked/'.$idpaket);
            }
        }
    }
    public function unduh($id)
    {
        $idpro = DB::table('kedatangan as k')
            ->join('rab_paket as r','k.id_rab_paket','=','r.id_rab_paket')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->join('paket as p','j.id_paket','=','p.id_paket')
            ->where('k.id_kedatangan','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/laporan/KEDATANGAN_".$idpro->id_paket."_".$idpro->id_kedatangan.".pdf";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, "KEDATANGAN_".$idpro->id_paket."_".$idpro->id_kedatangan.".pdf", $headers);
    }
    public function store()
    {
        DB::table('kedatangan')->insert(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'nama_material'            => Input::get('material'),
                'tgl_rencana_kedatangan'   => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_rencana_pengiriman'   => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'status_kedatangan'        => Input::get('status')
            )
        );
    }
    public function update()
    {
        DB::table('kedatangan')->where('id_kedatangan','=',Input::get('idked'))->update(
            array(   
                'id_rab_paket'            => Input::get('uraian'),
                'nama_material'            => Input::get('material'),
                'tgl_rencana_kedatangan'   => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_rencana_pengiriman'   => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'status_kedatangan'        => Input::get('status')
            )
        );
    }
}
