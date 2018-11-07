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

class PerencanaanController extends Controller
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
        $gambar = DB::table('gambar')->where('id_paket','=',$id)->get();
        return view('detail.perencanaan')->with('data', $data)->with('gambar',$gambar);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('gambar')
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
            return Redirect::to('/perencanaan/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            DB::table('gambar')->insert(
                array(   
                    'id_paket'           => $idpaket,
                    'nama_gambar'         => Input::get('Nama_Gambar'),
                    'lokasi_gambar'       => $filenya
                )
            );
            if ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/rks/'.$filenya,File::get($file));
                Session::flash('message', 'Data Gambar Perencanaan berhasil diunggah');
                return Redirect::to('/perencanaan/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/perencanaan/'.$idpaket);
            }
        }
    }
    

    public function downloadfile($id)
    {
        $idpro = DB::table('gambar')->where('id_gambar','=',$id)->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/rks/".$idpro->lokasi_gambar;
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, $idpro->nama_gambar.'_'.$idpro->id_paket.'.pdf', $headers);
        
    }
    public function hapus($id)
    {
        $idpro = DB::table('gambar')->where('id_gambar','=',$id)->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/rks/'.$idpro->lokasi_gambar;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('gambar')->where('id_gambar', '=',$id)->delete();
        Session::flash('message', 'Data Gambar Perencanaan berhasil dihapus !');
        return Redirect::to('/perencanaan/'.$idpro->id_paket);
    }
}
