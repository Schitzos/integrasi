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


class KualitasController extends Controller
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

    public function index($id)
    {
        $data = DB::table('paket')
            ->where('id_paket','=',$id)
            ->first();
        $kategori = DB::table('kategori_kualitas')->where('id_paket','=',$id)->get();
        $kualitas = DB::table('kualitas as k')
            ->join('kategori_kualitas as s','k.id_kategori_kualitas','=','s.id_kategori_kualitas')
            ->where('s.id_paket','=',$id)
            ->get();
        return view('detail.kualitas')->with('data', $data)->with('kategori', $kategori)->with('kualitas', $kualitas);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('kualitas')
                ->where('id_paket','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function simpankategori()
    {
      $idpaket = Input::get('idpaket');
      $rules = array(
          'Nama_Pekerjaan' => 'required'
      );      
      $messages = array(
          'required' => 'Kolom :attribute harus di isi.'
      );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
        return Redirect::to('/kualitas/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
        DB::table('kategori_kualitas')->insert(
        array( 
            'id_paket'                     => $idpaket,
            'nama_kategori_kualitas'        => Input::get('Nama_Pekerjaan'),
            'tgl_simpan_kategori_kualitas'  => date('Y-m-d'),
            'keterangan_kategori_kualitas'  => Input::get('Keterangan_Pekerjaaan'),
        ));
        Session::flash('message', 'Data Pekerjaan Kualitas Kinerja berhasil ditambahkan');
        return Redirect::to('/kualitas/'.$idpaket);
      }
    }
    public function store()
    {
        $idpaket = Input::get('idpaket1');
        $idkategori = Input::get("idkategori");
        $rules = array(
          'Nama_Foto'   => 'required',
          'gambar1'     => 'required',
          'gambar1'     => 'mimes:jpeg,jpg,png,gif'
        );      
        $messages = array(
          'required'    => 'Kolom :attribute harus di isi.',
          'mimes'       =>'Pilih Berkas Foto (*.jpeg,*.jpg,*.png,*.gif)'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {   
            return Redirect::to('/kualitas/'.$idpaket)->withErrors($validator)->withInput();
        }else{
            $file = Input::file('gambar1');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            Storage::disk('dokumentasi')->put($idpaket.'/kualitas/'.$file->getFilename().'.'.$extension,File::get($file));
            $filegambar = $file->getFilename().'.'.$extension;
            DB::table('kualitas')->insert(
            array( 
                'id_kategori_kualitas' => $idkategori,
                'nama_kualitas'        => Input::get('Nama_Foto'),
                'tgl_upload_kualitas'  => date('Y-m-d'),
                'lokasi_kualitas'      => $filegambar
            ));
            Session::flash('message', 'Data Kualitas Kinerja berhasil ditambahkan');
            return Redirect::to('/kualitas/'.$idpaket);
        }
    }
    public function destroy($id)
    {
        $idpro = DB::table('kualitas as u')
            ->join('kategori_kualitas as k','u.id_kategori_kualitas','=','k.id_kategori_kualitas')
            ->join('paket as p','k.id_paket','=','p.id_paket')
            ->where('u.id_kualitas','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/kualitas/'.$idpro->lokasi_kualitas;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('kualitas')->where('id_kualitas', '=',$id)->delete();
        Session::flash('message', 'Data Foto Kualitas Kinerja berhasil dihapus !');
        return Redirect::to('/kualitas/'.$idpro->id_paket);
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
            return Redirect::to('/kualitas/'.$id)->withErrors($validator)->withInput();
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
                    return Redirect::to('/kualitas/'.$id);
                } else {
                    Session::flash('eror', 'Password Konsultan yang Anda masukkan salah!!!');
                    return Redirect::to('/kualitas/'.$id);
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
                    return Redirect::to('/kualitas/'.$id);
                } else {
                    Session::flash('eror', 'Password Kontraktor yang Anda masukkan salah!!!');
                    return Redirect::to('/kualitas/'.$id);
                }
            }
        }
    }
}
