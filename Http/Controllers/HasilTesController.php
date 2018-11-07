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


class HasilTesController extends Controller
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
        $hasiltes = DB::table('hasil_test')
            ->where('id_paket','=',$id)
            ->get();
        return view('detail.hasiltes')->with('data', $data)->with('hasiltes',$hasiltes);
    }
    //struktur
    public function showdatastruktur(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('hasil_test as h')
                ->join('paket as p','h.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('h.tipe_hasil_test','=',0)
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showstruktur(Request $request, $id)
    {
        if ($request->ajax()) {
            $hasiltes = DB::table('hasil_test')
                ->where('id_paket','=',$id)
                ->where('tipe_hasil_test','=',0)
                ->get();
            return view('include.tabelhasilstruk')->with('hasiltes',$hasiltes); 
        }
    }
    public function simpanstruktur()
    {
        DB::table('hasil_test')->insert(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 0
            )
        );
    }
    public function ubahstruktur()
    {
        DB::table('hasil_test')->where('id_hasil_test','=',Input::get('idhasil'))->update(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 0
            )
        );
    }
    //arsitektur
    public function showdataarsitektur(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('hasil_test as h')
                ->join('paket as p','h.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('h.tipe_hasil_test','=',1)
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showarsitektur(Request $request, $id)
    {
        if ($request->ajax()) {
            $hasiltes = DB::table('hasil_test')
                ->where('id_paket','=',$id)
                ->where('tipe_hasil_test','=',1)
                ->get();
            return view('include.tabelhasilarsi')->with('hasiltes',$hasiltes); 
        }
    }
    public function simpanarsitektur()
    {
        DB::table('hasil_test')->insert(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 1
            )
        );
    }
    public function ubaharsitektur()
    {
        DB::table('hasil_test')->where('id_hasil_test','=',Input::get('idhasil'))->update(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 1
            )
        );
    }
    //mekanikal
    public function showdatamekanikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('hasil_test as h')
                ->join('paket as p','h.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('h.tipe_hasil_test','=',2)
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showmekanikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $hasiltes = DB::table('hasil_test')
                ->where('id_paket','=',$id)
                ->where('tipe_hasil_test','=',2)
                ->get();
            return view('include.tabelhasilmekanik')->with('hasiltes',$hasiltes); 
        }
    }
    public function simpanmekanikal()
    {
        DB::table('hasil_test')->insert(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 2
            )
        );
    }
    public function ubahmekanikal()
    {
        DB::table('hasil_test')->where('id_hasil_test','=',Input::get('idhasil'))->update(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 2
            )
        );
    }
    //elektronikal
    public function showdataelektrikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('hasil_test as h')
                ->join('paket as p','h.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('h.tipe_hasil_test','=',3)
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showelektrikal(Request $request, $id)
    {
        if ($request->ajax()) {
            $hasiltes = DB::table('hasil_test')
                ->where('id_paket','=',$id)
                ->where('tipe_hasil_test','=',3)
                ->get();
            return view('include.tabelhasilelektrik')->with('hasiltes',$hasiltes); 
        }
    }
    public function simpanelektrikal()
    {
        DB::table('hasil_test')->insert(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 3
            )
        );
    }
    public function ubahelektrikal()
    {
        DB::table('hasil_test')->where('id_hasil_test','=',Input::get('idhasil'))->update(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      =>Input::get('statuse'),
                'tipe_hasil_test'          => 3
            )
        );
    }
    //plubing
    public function showdataplumbing(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('hasil_test as h')
                ->join('paket as p','h.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('h.tipe_hasil_test','=',4)
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showplumbing(Request $request, $id)
    {
        if ($request->ajax()) {
            $hasiltes = DB::table('hasil_test')
                ->where('id_paket','=',$id)
                ->where('tipe_hasil_test','=',4)
                ->get();
            return view('include.tabelhasilplumbing')->with('hasiltes',$hasiltes); 
        }
    }
    public function simpanplumbing()
    {
        DB::table('hasil_test')->insert(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      => Input::get('statuse'),
                'tipe_hasil_test'          => 4
            )
        );
    }
    public function ubahplumbing()
    {
        DB::table('hasil_test')->where('id_hasil_test','=',Input::get('idhasil'))->update(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      => Input::get('statuse'),
                'tipe_hasil_test'          => 4
            )
        );
    }
    //lain2x
    public function showdatalain(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('hasil_test as h')
                ->join('paket as p','h.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('h.tipe_hasil_test','=',5)
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showlain(Request $request, $id)
    {
        if ($request->ajax()) {
            $hasiltes = DB::table('hasil_test')
                ->where('id_paket','=',$id)
                ->where('tipe_hasil_test','=',5)
                ->get();
            return view('include.tabelhasillain')->with('hasiltes',$hasiltes); 
        }
    }
    public function simpanlain()
    {
        DB::table('hasil_test')->insert(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      => Input::get('statuse'),
                'tipe_hasil_test'          => 5
            )
        );
    }
    public function ubahlain()
    {
        DB::table('hasil_test')->where('id_hasil_test','=',Input::get('idhasil'))->update(
            array(   
                'id_paket'                => Input::get('id'),
                'rencana_hasil_test'       => Input::get('rencana'),
                'tgl_masuk_hasil_test'     => date('Y-m-d',strtotime(Input::get('tgl1'))),
                'tgl_hasil_test'           => date('Y-m-d',strtotime(Input::get('tgl2'))),
                'uraian_hasil_test'        => Input::get('uraian'),
                'kualitas_hasil_test'      => Input::get('statuse'),
                'tipe_hasil_test'          => 5
            )
        );
    }
    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $idked = Input::get('idhasil');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/teshasil/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='pdf'||$extension=='PDF') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/rks/HASILUJI_'.$idpaket.'_'.$idked.'.pdf';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/rks/HASILUJI_'.$idpaket.'_'.$idked.'.PDF';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/rks/HASILUJI_'.$idpaket.'_'.$idked.'.'.$extension,File::get($file));
                Session::flash('message', 'Data Bukti Hasil Tes Kualitas berhasil diunggah');
                return Redirect::to('/teshasil/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/teshasil/'.$idpaket);
            }
        }
    }
    public function unduh($id)
    {
        $idpro = DB::table('hasil_test as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_hasil_test','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rks/HASILUJI_".$idpro->id_paket."_".$idpro->id_hasil_test.".pdf";
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, "HASILUJI_".$idpro->id_paket."_".$idpro->id_hasil_test.".pdf", $headers);
    }
}
