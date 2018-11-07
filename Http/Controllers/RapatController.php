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


class RapatController extends Controller
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
        $rapat = DB::table('rapat')
                ->where('id_paket','=',$id)
                ->get();
        return view('detail.rapat')->with('data', $data)->with('rapat',$rapat);
    }
    //struktur
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('rapat')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showdetail(Request $request, $id)
    {
        if ($request->ajax()) {
            $rapat = DB::table('rapat')
                ->where('id_paket','=',$id)
                ->get();
            return view('include.tabelrapat')->with('rapat',$rapat); 
        }
    }
    public function simpan()
    {
        DB::table('rapat')->insert(
            array(   
                'id_paket' => Input::get('id'),
                'tgl_rapat' => date('Y-m-d',strtotime(Input::get('tgl')))
            )
        );
    }
    public function ubah()
    {
        DB::table('rapat')->where('id_rapat','=',Input::get('idrap'))->update(
            array(
                'tgl_rapat' => date('Y-m-d',strtotime(Input::get('tgl'))),
            )
        );
    }
    
    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $idrap = Input::get('idrap');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/rapat/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='pdf'||$extension=='PDF') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/pelaksanaan/RAPAT_'.$idpaket.'_'.$idrap.'.pdf';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/pelaksanaan/RAPAT_'.$idpaket.'_'.$idrap.'.PDF';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/pelaksanaan/RAPAT_'.$idpaket.'_'.$idrap.'.'.$extension,File::get($file));
                Session::flash('message', 'Data Dokumen Hasil Rapat berhasil diunggah');
                return Redirect::to('/rapat/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/rapat/'.$idpaket);
            }
        }
    }
    public function unduh($id)
    {
        $idpro = DB::table('rapat')
            ->where('id_rapat','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/pelaksanaan/RAPAT_".$idpro->id_paket."_".$idpro->id_rapat.".pdf";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, "RAPAT_".$idpro->id_paket."_".$idpro->id_rapat.".pdf", $headers);
    }
}
