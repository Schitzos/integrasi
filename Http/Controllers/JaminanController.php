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


class JaminanController extends Controller
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
        $jaminan = DB::table('garansi')->where('id_paket','=',$id)->get();
        return view('detail.jaminan')->with('data', $data)->with('jaminan', $jaminan);
    }

    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('garansi as g')
                ->join('paket as p','g.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showdetail(Request $request, $id)
    {
        if ($request->ajax()) {
            $jaminan = DB::table('garansi as g')
                ->join('paket as p','g.id_paket','=','p.id_paket')
                ->where('p.id_paket','=',$id)
                ->get();
            return view('include.tabeljaminan')->with('jaminan',$jaminan); 
        }
    }
    public function simpan()
    {
        DB::table('garansi')->insert(
            array(   
                'id_paket'             => Input::get('id'),
                'uraian_garansi'        => Input::get('uraian'),
                'keterangan_garansi'    => Input::get('keterangan')
            )
        );
    }
    public function ubah()
    {
        DB::table('garansi')->where('id_garansi','=',Input::get('idgaransi'))->update(
            array(   
                'id_paket'             => Input::get('id'),
                'uraian_garansi'        => Input::get('uraian'),
                'keterangan_garansi'    => Input::get('keterangan')
            )
        );
    }

    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $idked = Input::get('idgaransi');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/jaminan/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='pdf'||$extension=='PDF') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/rks/GARANSI_'.$idpaket.'_'.$idked.'.pdf';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/rks/GARANSI_'.$idpaket.'_'.$idked.'.PDF';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/rks/GARANSI_'.$idpaket.'_'.$idked.'.'.$extension,File::get($file));
                Session::flash('message', 'Data Bukti Garansi berhasil diunggah');
                return Redirect::to('/jaminan/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/jaminan/'.$idpaket);
            }
        }
    }
    public function unduh($id)
    {
        $idpro = DB::table('garansi as g')
            ->join('paket as p','g.id_paket','=','p.id_paket')
            ->where('g.id_garansi','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rks/GARANSI_".$idpro->id_paket."_".$idpro->id_garansi.".pdf";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, "GARANSI_".$idpro->id_paket."_".$idpro->id_garansi.".pdf", $headers);
    }
    
}
