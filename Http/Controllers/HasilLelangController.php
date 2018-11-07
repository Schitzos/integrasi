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


class HasilLelangController extends Controller
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
        $bahp = DB::table('hasil_bahp as b')
            ->join('paket as p','b.id_paket','=','p.id_paket')
            ->where('p.id_paket','=',$id)
            ->get();
        $klarifikasi = DB::table('hasil_klarifikasi as k')
            ->join('paket as p','k.id_paket','=','p.id_paket')
            ->where('p.id_paket','=',$id)
            ->get();
        $penawaran = DB::table('hasil_penawaran as w')
            ->join('paket as p','w.id_paket','=','p.id_paket')
            ->where('p.id_paket','=',$id)
            ->get();
        $hps = DB::table('hasil_hps as k')
            ->join('paket as p','k.id_paket','=','p.id_paket')
            ->where('p.id_paket','=',$id)
            ->get();
        return view('detail.hasillelang')->with('data', $data)->with('bahp',$bahp)->with('klarifikasi',$klarifikasi)
        ->with('penawaran',$penawaran)->with('hps',$hps);
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
    public function simpanbahp()
    {
        $idpaket = Input::get('idpaket');
        $rules = array(
            'Nama_File' => 'required',
            'Tgl_BAHP' => 'required',
            'PDFBHAP' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/hasillelang/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('PDFBHAP');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_bahp')->insert(
                array( 
                    'id_paket'         => $idpaket,
                    'nama_bahp'         => Input::get('Nama_File'),
                    'lokasi_bahp'       => $filenya
                ));
                Session::flash('message', 'Data Hasil BAHP berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } elseif ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_bahp')->insert(
                array( 
                    'id_paket'         => $idpaket,
                    'nama_bahp'         => Input::get('Nama_File'),
                    'lokasi_bahp'       => $filenya,
                    'tgl_bahp'          => date('y-m-d',strtotime(Input::get('Tgl_BAHP'))) 
                ));
                Session::flash('message', 'Data Hasil BAHP berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe pdf atau excel (*.pdf,*xls,*xlsx) !!');
                return Redirect::to('/hasillelang/'.$idpaket);
            }
        }
    }
    public function downloadbahp($id)
    {
        $idpro = DB::table('hasil_bahp as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_bahp','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rab/".$idpro->lokasi_bahp;
        $tipe = explode('.', $idpro->lokasi_bahp);
        if ($tipe[1]=='xls'||$tipe[1]=='xlsx') {
            $headers = array(
                  'Content-Type: application/excel',
                );
            return Response::download($file, 'Lelang_BAHP_'.$idpro->id_paket.'.xls', $headers);
        } else {
            $headers = array(
                  'Content-Type: application/pdf',
                );
            return Response::download($file, 'Lelang_BAHP_'.$idpro->id_paket.'.pdf', $headers);
        }
    }
    public function hapusbahp($id)
    {
        $idpro = DB::table('hasil_bahp as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_bahp','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/rab/'.$idpro->lokasi_bahp;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('hasil_bahp')->where('id_bahp', '=',$id)->delete();
        Session::flash('message', 'Data Hasil Lelang BAHP berhasil dihapus !');
        return Redirect::to('/hasillelang/'.$idpro->id_paket);
    }

    public function simpannego()
    {
        $idpaket = Input::get('idpaketnego');
        $rules = array(
            'Nama_File' => 'required',
            'PDFNEGOSIASI' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/hasillelang/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('PDFNEGOSIASI');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_klarifikasi')->insert(
                array( 
                    'id_paket'             => $idpaket,
                    'nama_klarifikasi'      => Input::get('Nama_File'),
                    'lokasi_klarifikasi'    => $filenya
                ));
                Session::flash('message', 'Data Hasil Lelang Negosiasi berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } elseif ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_klarifikasi')->insert(
                array( 
                    'id_paket'                => $idpaket,
                    'nama_klarifikasi'         => Input::get('Nama_File'),
                    'lokasi_klarifikasi'       => $filenya
                ));
                Session::flash('message', 'Data Hasil Lelang Negosiasi berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe pdf atau excel (*.pdf,*xls,*xlsx) !!');
                return Redirect::to('/hasillelang/'.$idpaket);
            }
        }
    }
    public function downloadnego($id)
    {
        $idpro = DB::table('hasil_klarifikasi as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_klarifikasi','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rab/".$idpro->lokasi_klarifikasi;
        $tipe = explode('.', $idpro->lokasi_klarifikasi);
        if ($tipe[1]=='xls'||$tipe[1]=='xlsx') {
            $headers = array(
                  'Content-Type: application/excel',
                );
            return Response::download($file, 'Lelang_Negosiasi_'.$idpro->id_paket.'.xls', $headers);
        } else {
            $headers = array(
                  'Content-Type: application/pdf',
                );
            return Response::download($file, 'Lelang_Negosiasi_'.$idpro->id_paket.'.pdf', $headers);
        }
    }
    public function hapusnego($id)
    {
        $idpro = DB::table('hasil_klarifikasi as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_klarifikasi','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/rab/'.$idpro->lokasi_klarifikasi;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('hasil_klarifikasi')->where('id_klarifikasi', '=',$id)->delete();
        Session::flash('message', 'Data Hasil Lelang Negosiasi berhasil dihapus !');
        return Redirect::to('/hasillelang/'.$idpro->id_paket);
    }

    public function simpanpenawaran()
    {
        $idpaket = Input::get('idpakettawar');
        $rules = array(
            'Nama_File' => 'required',
            'PDFPENAWARAN' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/hasillelang/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('PDFPENAWARAN');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_penawaran')->insert(
                array( 
                    'id_paket'         => $idpaket,
                    'nama_penawaran'    => Input::get('Nama_File'),
                    'lokasi_penawaran'  => $filenya
                ));
                Session::flash('message', 'Data Hasil Lelang RAB Penawaran berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } elseif ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_penawaran')->insert(
                array( 
                    'id_paket'         => $idpaket,
                    'nama_penawaran'    => Input::get('Nama_File'),
                    'lokasi_penawaran'  => $filenya
                ));
                Session::flash('message', 'Data Hasil Lelang RAB Penawaran berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe pdf atau excel (*.pdf,*xls,*xlsx) !!');
                return Redirect::to('/hasillelang/'.$idpaket);
            }
        }
    }
    public function downloadpenawaran($id)
    {
        $idpro = DB::table('hasil_penawaran as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_penawaran','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rab/".$idpro->lokasi_penawaran;
        $tipe = explode('.', $idpro->lokasi_penawaran);
        if ($tipe[1]=='xls'||$tipe[1]=='xlsx') {
            $headers = array(
                  'Content-Type: application/excel',
                );
            return Response::download($file, 'Lelang_Penawaran_'.$idpro->id_paket.'.xls', $headers);
        } else {
            $headers = array(
                  'Content-Type: application/pdf',
                );
            return Response::download($file, 'Lelang_Penawaran_'.$idpro->id_paket.'.pdf', $headers);
        }
    }
    public function hapuspenawaran($id)
    {
        $idpro = DB::table('hasil_penawaran as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_penawaran','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/rab/'.$idpro->lokasi_penawaran;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('hasil_penawaran')->where('id_penawaran', '=',$id)->delete();
        Session::flash('message', 'Data Hasil Lelang RAB Penawaran berhasil dihapus !');
        return Redirect::to('/hasillelang/'.$idpro->id_paket);
    }

    public function simpanhps()
    {
        $idpaket = Input::get('idpakethps');
        $rules = array(
            'Nama_File' => 'required',
            'PDFHPS' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/hasillelang/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('PDFHPS');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_hps')->insert(
                array( 
                    'id_paket'   => $idpaket,
                    'nama_hps'    => Input::get('Nama_File'),
                    'lokasi_hps'  => $filenya
                ));
                Session::flash('message', 'Data Hasil Lelang HPS berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } elseif ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/rab/'.$file->getFilename().'.'.$extension,File::get($file));
                DB::table('hasil_hps')->insert(
                array( 
                    'id_paket'   => $idpaket,
                    'nama_hps'    => Input::get('Nama_File'),
                    'lokasi_hps'  => $filenya
                ));
                Session::flash('message', 'Data Hasil Lelang HPS berhasil ditambahkan');
                return Redirect::to('/hasillelang/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe pdf atau excel (*.pdf,*xls,*xlsx) !!');
                return Redirect::to('/hasillelang/'.$idpaket);
            }
        }
    }
    public function downloadhps($id)
    {
        $idpro = DB::table('hasil_hps as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_hps','=',$id)
            ->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rab/".$idpro->lokasi_hps;
        $tipe = explode('.', $idpro->lokasi_hps);
        if ($tipe[1]=='xls'||$tipe[1]=='xlsx') {
            $headers = array(
                  'Content-Type: application/excel',
                );
            return Response::download($file, 'Lelang_HPS_'.$idpro->id_paket.'.xls', $headers);
        } else {
            $headers = array(
                  'Content-Type: application/pdf',
                );
            return Response::download($file, 'Lelang_HPS_'.$idpro->id_paket.'.pdf', $headers);
        }
    }
    public function hapushps($id)
    {
        $idpro = DB::table('hasil_hps as h')
            ->join('paket as p','h.id_paket','=','p.id_paket')
            ->where('h.id_hps','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/rab/'.$idpro->lokasi_hps;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('hasil_hps')->where('id_hps', '=',$id)->delete();
        Session::flash('message', 'Data Hasil Lelang HPS berhasil dihapus !');
        return Redirect::to('/hasillelang/'.$idpro->id_paket);
    }
}
