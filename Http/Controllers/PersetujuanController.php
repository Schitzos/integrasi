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


class PersetujuanController extends Controller
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
        $persetujuan = DB::table('outline as o')
                ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('j.id_paket','=',$id)
                ->get();
        $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
        return view('detail.persetujuan')->with('data', $data)->with('persetujuan',$persetujuan)->with('rabpaket',$rabpaket);
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
            $data = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.tipe_outline','=',0)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showstruktur(Request $request, $id)
    {
        if ($request->ajax()) {
            $persetujuan = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('p.tipe_outline','=',0)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelsetstruk')->with('persetujuan',$persetujuan)->with('rabpaket',$rabpaket); 
        }
    }
    
    public function ubahstruktur()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idper'))->update(
            array(   
                'status_outline'       => Input::get('status'),
                'tgl_outline'          => date('Y-m-d',strtotime(Input::get('tgl'))),
            )
        );
    }
    //arsitektur
    public function showdataarsitektur(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.tipe_outline','=',1)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showarsitektur(Request $request, $id)
    {
        if ($request->ajax()) {
            $persetujuan = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('p.tipe_outline','=',1)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelsetarsi')->with('persetujuan',$persetujuan)->with('rabpaket',$rabpaket); 
        }
    }
    public function ubaharsitektur()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idper'))->update(
            array(   
                'status_outline'       => Input::get('status'),
                'tgl_outline'          => date('Y-m-d',strtotime(Input::get('tgl'))),
            )
        );
    }
    //mekanikal
    public function showdatamekanikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.tipe_outline','=',2)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showmekanikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $persetujuan = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('p.tipe_outline','=',2)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelsetmekanik')->with('persetujuan',$persetujuan)->with('rabpaket',$rabpaket); 
        }
    }
    
    public function ubahmekanikal()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idper'))->update(
            array(   
               'status_outline'       => Input::get('status'),
               'tgl_outline'          => date('Y-m-d',strtotime(Input::get('tgl'))),
            )
        );
    }
    //elektrikal
    public function showdataelektrikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.tipe_outline','=',3)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showelektrikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $persetujuan = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('p.tipe_outline','=',3)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelsetelektronik')->with('persetujuan',$persetujuan)->with('rabpaket',$rabpaket); 
        }
    }

    public function ubahelektrikal()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idper'))->update(
            array(   
                'status_outline'       => Input::get('status'),
                'tgl_outline'          => date('Y-m-d',strtotime(Input::get('tgl'))),
            )
        );
    }
    //plumbing
    public function showdataplumbing(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.tipe_outline','=',4)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showplumbing(Request $request, $id)
    {
        if ($request->ajax()) {
            $persetujuan = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('p.tipe_outline','=',4)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelsetplumbing')->with('persetujuan',$persetujuan)->with('rabpaket',$rabpaket); 
        }
    }
    public function ubahplumbing()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idper'))->update(
            array(   
                'status_outline'       => Input::get('status'),
                'tgl_outline'          => date('Y-m-d',strtotime(Input::get('tgl'))),
            )
        );
    }
    //lain
    public function showdatalain(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.tipe_outline','=',5)
                ->where('j.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showlain(Request $request, $id)
    {
        if ($request->ajax()) {
            $persetujuan = DB::table('outline as p')
                ->join('rab_paket as r','p.id_rab_paket','=','r.id_rab_paket')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('p.tipe_outline','=',5)
                ->where('j.id_paket','=',$id)
                ->get();
            $rabpaket = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->where('j.id_paket','=',$id)
            ->get();
            return view('include.tabelsetlain')->with('persetujuan',$persetujuan)->with('rabpaket',$rabpaket); 
        }
    }
    public function ubahlain()
    {
        DB::table('outline')->where('id_outline','=',Input::get('idper'))->update(
            array(   
                'status_outline'       => Input::get('status'),
                'tgl_outline'          => date('Y-m-d',strtotime(Input::get('tgl'))),
            )
        );
    }
    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $idked = Input::get('idper');
        $tipene = Input::get('tipene');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/persetujuan/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='pdf'||$extension=='PDF') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/rks/PERSETUJUAN_'.$idpaket.'_'.$idked.'.pdf';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/rks/PERSETUJUAN_'.$idpaket.'_'.$idked.'.PDF';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/rks/PERSETUJUAN_'.$idpaket.'_'.$idked.'.'.$extension,File::get($file));
                Session::flash('message', 'Data Bukti Persetujuan berhasil diunggah');
                return Redirect::to('/persetujuan/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/persetujuan/'.$idpaket);
            }
        }
    }
    public function unduh($id)
    {
        $idpro = DB::table('outline as o')
            ->join('rab_paket as r','o.id_rab_paket','=','r.id_rab_paket')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->join('paket as p','j.id_paket','=','p.id_paket')
            ->where('o.id_outline','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rks/PERSETUJUAN_".$idpro->id_paket."_".$idpro->id_outline.".pdf";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, "PERSETUJUAN_".$idpro->id_paket."_".$idpro->id_outline.".pdf", $headers);
    }
}
