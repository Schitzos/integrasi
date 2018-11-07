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

class DokumenRKSController extends Controller
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
        $jadwal = DB::table('jadwal')->where('id_paket','=',$id)->get();
        $dokrks = DB::table('dok_rks')->where('id_paket','=',$id)->get();
        return view('detail.dokrks')->with('data', $data)->with('jadwal',$jadwal)->with('dokrks',$dokrks);
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
    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/dokrks/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            DB::table('dok_rks')->insert(
                array(   
                    'id_paket'            => $idpaket,
                    'nama_dok_rks'         => Input::get('Nama_Dokumen'),
                    'lokasi_dok_rks'       => $filenya,
                    'tgl_upload_dok_rks'   => date('Y-m-d')
                )
            );
            if ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/rks/'.$file->getFilename().'.'.$extension,File::get($file));
                Session::flash('message', 'Data Dokumen RKS berhasil diunggah');
                return Redirect::to('/dokrks/'.$idpaket);
            } elseif ($extension=='doc'||$extension=='docx') {
                Storage::disk('dokumentasi')->put($idpaket.'/rks/'.$file->getFilename().'.'.$extension,File::get($file));
                Session::flash('message', 'Data Dokumen RKS berhasil diunggah');
                return Redirect::to('/dokrks/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF atau Word (*.pdf,*.PDF,*.doc,*.docx) !!');
                return Redirect::to('/dokrks/'.$idpaket);
            }
        }
    }

    public function unduh($id)
    {
        $idpro = DB::table('dok_rks')->where('id_dok_rks','=',$id)->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rks/".$idpro->lokasi_dok_rks;
        $tipe = explode('.', $idpro->lokasi_dok_rks);
        if ($tipe[1]=='doc'||$tipe[1]=='docx') {
            $headers = array(
              'Content-Type: application/word',
            );
            return Response::download($file, 'DOKUMEN_RKS_'.$idpro->id_dok_rks.'.docx', $headers);
        } else {
           $headers = array(
              'Content-Type: application/pdf',
            );
            return Response::download($file, 'DOKUMEN_RKS_'.$idpro->id_dok_rks.'.pdf', $headers);
        }
    }
    public function hapus($id)
    {
        $idpro = DB::table('dok_rks')->where('id_dok_rks','=',$id)->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/rks/'.$idpro->lokasi_dok_rks;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('dok_rks')->where('id_dok_rks', '=',$id)->delete();
        Session::flash('message', 'Data Dokumen RKS berhasil dihapus !');
        return Redirect::to('/dokrks/'.$idpro->id_paket);
    }
}
