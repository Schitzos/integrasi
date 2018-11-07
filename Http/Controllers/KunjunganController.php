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


class KunjunganController extends Controller
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
        $kategori = DB::table('kategori_kunjungan')->where('id_paket','=',$id)->get();
        $kunjungan = DB::table('kunjungan as k')
            ->join('kategori_kunjungan as s','k.id_kategori_kunjungan','=','s.id_kategori_kunjungan')
            ->where('s.id_paket','=',$id)
            ->get();
        return view('detail.kunjungan')->with('data', $data)->with('kategori', $kategori)->with('kunjungan', $kunjungan);
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
          'Nama_Kunjungan' => 'required'
      );      
      $messages = array(
          'required' => 'Kolom :attribute harus di isi.'
      );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
        return Redirect::to('/kunjungan/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
        DB::table('kategori_kunjungan')->insert(
        array( 
            'id_paket'                      => $idpaket,
            'nama_kategori_kunjungan'        => Input::get('Nama_Kunjungan'),
            'tgl_simpan_kategori_kunjungan'  => date('Y-m-d',strtotime(Input::get('Tanggal_Kunjungan'))),
            'keterangan_kategori_kunjungan'  => Input::get('Keterangan_Kunjungan'),
        ));
        Session::flash('message', 'Data Jenis Kunjungan berhasil ditambahkan');
        return Redirect::to('/kunjungan/'.$idpaket);
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
          'mimes'       => 'Pilih Berkas Foto (*.jpeg,*.jpg,*.png,*.gif)'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {   
            return Redirect::to('/kunjungan/'.$idpaket)->withErrors($validator)->withInput();
        }else{
            $file = Input::file('gambar1');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            Storage::disk('dokumentasi')->put($idpaket.'/kunjungan/'.$file->getFilename().'.'.$extension,File::get($file));
            $filegambar = $file->getFilename().'.'.$extension;
            DB::table('kunjungan')->insert(
            array( 
                'id_kategori_kunjungan' => $idkategori,
                'nama_kunjungan'        => Input::get('Nama_Foto'),
                'tgl_upload_kunjungan'  => date('Y-m-d'),
                'lokasi_kunjungan'      => $filegambar
            ));
            Session::flash('message', 'Data Kunjungan berhasil ditambahkan');
            return Redirect::to('/kunjungan/'.$idpaket);
        }
    }
    public function destroy($id)
    {
        $idpro = DB::table('kunjungan as u')
            ->join('kategori_kunjungan as k','u.id_kategori_kunjungan','=','k.id_kategori_kunjungan')
            ->join('paket as p','k.id_paket','=','p.id_paket')
            ->where('u.id_kunjungan','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/kunjungan/'.$idpro->lokasi_kunjungan;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('kunjungan')->where('id_kunjungan', '=',$id)->delete();
        Session::flash('message', 'Data Foto Kunjungan berhasil dihapus !');
        return Redirect::to('/kunjungan/'.$idpro->id_paket);
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
            return Redirect::to('/kunjungan/'.$id)->withErrors($validator)->withInput();
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
                    return Redirect::to('/kunjungan/'.$id);
                } else {
                    Session::flash('eror', 'Password Konsultan yang Anda masukkan salah!!!');
                    return Redirect::to('/kunjungan/'.$id);
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
                    return Redirect::to('/kunjungan/'.$id);
                } else {
                    Session::flash('eror', 'Password Kontraktor yang Anda masukkan salah!!!');
                    return Redirect::to('/kunjungan/'.$id);
                }
            }
        }
    }
}
