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


class VideoController extends Controller
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
        $kategori = DB::table('kategori_video')->where('id_paket','=',$id)->get();
        $video = DB::table('video as v')
            ->join('kategori_video as k','v.id_kategori_video','=','k.id_kategori_video')
            ->where('k.id_paket','=',$id)
            ->get();
        return view('detail.video')->with('data', $data)->with('kategori', $kategori)->with('video', $video);
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
    public function simpankategori()
    {
      $idpaket = Input::get('idpaket');
      $rules = array(
          'Nama_Kategori' => 'required'
      );      
      $messages = array(
          'required' => 'Kolom :attribute harus di isi.'
      );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
        return Redirect::to('/video/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
        DB::table('kategori_video')->insert(
        array( 
            'id_paket'                 => $idpaket,
            'nama_kategori_video'       => Input::get('Nama_Kategori'),
            'tgl_simpan_kategori_video' => date('Y-m-d'),
            'keterangan_kategori_video' => Input::get('Keterangan_Kategori'),
        ));
        Session::flash('message', 'Data Kategori Video berhasil ditambahkan');
        return Redirect::to('/video/'.$idpaket);
      }
    }
    public function store()
    {
      $idpaket = Input::get('idpaket1');
      $idkategori = Input::get("idkategori");
    
      $rules = array(
          'Nama_Video' => 'required',
          'gambar1' => 'required',
          'gambar1' => 'mimes:mp4,avi,mpeg,flv,mkv,3gp'
      );      
      $messages = array(
          'required'  => 'Kolom :attribute harus di isi.',
          'mimes'     => 'Pilih Berkas Foto (*.avi,*.mp4,*.mpeg,*.mkv,*.flv,*.3gp)'
      );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
        return Redirect::to('/video/'.$idpaket)->withErrors($validator)->withInput();
      }else{
        $file = Input::file('gambar1');
        $extension = $file->getClientOriginalExtension();
        $mimetype = $file->getClientMimeType();
        Storage::disk('dokumentasi')->put($idpaket.'/video/'.$file->getFilename().'.'.$extension,File::get($file));
        $filegambar = $file->getFilename().'.'.$extension;
        DB::table('video')->insert(
        array( 
            'id_kategori_video' => $idkategori,
            'nama_video'        => Input::get('Nama_Video'),
            'tgl_upload_video'  => date('Y-m-d'),
            'lokasi_video'      => $filegambar
        ));
        Session::flash('message', 'Data Video Progress berhasil ditambahkan');
        return Redirect::to('/video/'.$idpaket);
    }
  }
  public function destroy($id)
    {
        $idpro = DB::table('video as v')
            ->join('kategori_video as k','v.id_kategori_video','=','k.id_kategori_video')
            ->join('paket as p','k.id_paket','=','p.id_paket')
            ->where('v.id_video','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/video/'.$idpro->lokasi_video;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('video')->where('id_video', '=',$id)->delete();
        Session::flash('message', 'Data Video Progress berhasil dihapus !');
        return Redirect::to('/video/'.$idpro->id_paket);
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
            return Redirect::to('/video/'.$id)->withErrors($validator)->withInput();
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
                    return Redirect::to('/video/'.$id);
                } else {
                    Session::flash('eror', 'Password Konsultan yang Anda masukkan salah!!!');
                    return Redirect::to('/video/'.$id);
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
                    return Redirect::to('/video/'.$id);
                } else {
                    Session::flash('eror', 'Password Kontraktor yang Anda masukkan salah!!!');
                    return Redirect::to('/video/'.$id);
                }
            }
        }
    }
}
