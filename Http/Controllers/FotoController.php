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


class FotoController extends Controller
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
        $foto = DB::table('foto as f')->join('jadwal as j','f.id_jadwal','=','j.id_jadwal')
                ->where('j.id_paket','=',$id)->get();
        return view('detail.foto')->with('data', $data)->with('jadwal',$jadwal)->with('foto',$foto);
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
    public function store()
    {
        $idjadwal = Input::get('idjadwal');
        $idpaket = Input::get('idpaket');
        $kejadwal = Input::get('jadwalke');
        $rules = array(
            'Nama_Foto' => 'required|max:255',
            'gambar1' => 'required',
            'gambar1' => 'mimes:jpeg,jpg,png,gif'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.',
            'max' => 'Panjang :attribute tidak boleh melebihi :max karakter.',
            'mimes'=>'Pilih Berkas Foto (*.jpeg,*.jpg,*.png,*.gif)'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/foto/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('gambar1');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filegambar = $file->getFilename().'.'.$extension;
            Storage::disk('dokumentasi')->put($idpaket.'/foto/Minggu_Ke_'.$kejadwal.'/'.$file->getFilename().'.'.$extension,File::get($file));
            DB::table('foto')->insert(
            array( 
                'id_jadwal'         => $idjadwal,
                'nama_foto'         => Input::get('Nama_Foto'),
                'lokasi_foto'       => $filegambar,
                'tgl_upload_foto'   => date('Y-m-d')
            ));
            Session::flash('message', 'Data Foto Progress berhasil ditambahkan');
            return Redirect::to('/foto/'.$idpaket);
        }
    }
    public function destroy($id)
    {
        $idpro = DB::table('foto as f')
            ->join('jadwal as j','f.id_jadwal','=','j.id_jadwal')
            ->join('paket as p','j.id_paket','=','p.id_paket')
            ->where('f.id_foto','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/foto/Minggu_Ke_'.$idpro->ke_jadwal.'/'.$idpro->lokasi_foto;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('foto')->where('id_foto', '=',$id)->delete();
        Session::flash('message', 'Data Foto Progress berhasil dihapus !');
        return Redirect::to('/foto/'.$idpro->id_paket);
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
            return Redirect::to('/foto/'.$id)->withErrors($validator)->withInput();
        }else 
        {
            if ($tipenya==5) {
                $proyek = DB::table('paket')->where('id_paket','=',$id)->first();
                $konsultan = $proyek->id_konsultan;
                $konsultannya = DB::table('admin')->where('id_konsultan','=',$konsultan)->first();
                if ($konsultannya->andropass==MD5(Input::get('Passnya'))) {
                    DB::table('admin')->where('id','=',$usernya)->update(
                    array(   
                        'status_pass'   => 1,
                        'lawan_lawas'   => $konsultan
                    ));
                    Session::flash('message', 'Kunci Ubah Data Proyek Berhasil Dibuka');
                    return Redirect::to('/foto/'.$id);
                } else {
                    Session::flash('eror', 'Password Konsultan yang Anda masukkan salah!!!');
                    return Redirect::to('/foto/'.$id);
                }
            }else{
                $proyek = DB::table('paket')->where('id_paket','=',$id)->first();
                $kontraktor = $proyek->id_kontraktor;
                $kontraktornya = DB::table('admin')->where('id_kontraktor','=',$kontraktor)->first();
                if ($kontraktornya->andropass==MD5(Input::get('Passnya'))) {
                    DB::table('admin')->where('id','=',$usernya)->update(
                    array(   
                        'status_pass'   => 1,
                        'lawan_lawas'   => $kontraktor
                    ));
                    Session::flash('message', 'Kunci Ubah Data Proyek Berhasil Dibuka');
                    return Redirect::to('/foto/'.$id);
                } else {
                    Session::flash('eror', 'Password Kontraktor yang Anda masukkan salah!!!');
                    return Redirect::to('/foto/'.$id);
                }
            }
        }
    }
}
