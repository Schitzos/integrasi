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


class PengendalianController extends Controller
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
        $data = DB::table('proyek')
            ->where('id_proyek','=',$id)
            ->first();
        return view('detail.formpengendalian')->with('data', $data);
    }

    public function unduhpengendalian(){
        $file= public_path(). "/images/FORMULIR_PENGENDALIAN_KONTRUKSI.xls";
        $headers = array(
                  'Content-Type: application/excel',
                );
        return Response::download($file, 'FORMULIR_PENGENDALIAN_KONTRUKSI.xls', $headers);
    }

    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('sop')
                ->where('id_proyek','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function store()
    {
        $idproyek = Input::get('idproyek');
        $rules = array(
            'Nama_File' => 'required',
            'gambar1' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/pengendalian/'.$idproyek)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('gambar1');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                Storage::disk('dokumentasi')->put($idproyek.'/pelaksanaan/SOPPEKERJAAN_'.$idproyek.'.'.$extension,File::get($file));
                DB::table('sop')->insert(
                array( 
                    'id_proyek'         => $idproyek,
                    'nama_file_sop'     => Input::get('Nama_File'),
                    'lokasi_sop'        => $filenya
                ));
                Session::flash('message', 'Data SOP Pekerjaan berhasil ditambahkan');
                return Redirect::to('/soppek/'.$idproyek);
            } elseif ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idproyek.'/pelaksanaan/SOPPEKERJAAN_'.$idproyek.'.'.$extension,File::get($file));
                DB::table('sop')->insert(
                array( 
                    'id_proyek'        => $idproyek,
                    'nama_file_sop'    => Input::get('Nama_File'),
                    'lokasi_sop'       => $filenya
                ));
                Session::flash('message', 'Data SOP Pekerjaan berhasil ditambahkan');
                return Redirect::to('/soppek/'.$idproyek);
            } else {
                Session::flash('eror', 'Pilih File Bertipe pdf atau excel (*.pdf,*xls,*xlsx) !!');
                return Redirect::to('/pengendalian/'.$idproyek);
            }
        }
    }
    public function destroy($id)
    {
        $idpro = DB::table('sop as s')
            ->join('proyek as p','s.id_proyek','=','p.id_proyek')
            ->where('s.id_sop','=',$id)
            ->first();
        $path = 'images/dokumentasi/'.$idpro->id_proyek.'/pelaksanaan/'.$idpro->lokasi_sop;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('sop')->where('id_sop', '=',$id)->delete();
        Session::flash('message', 'Data SOP Pekerjaan berhasil dihapus !');
        return Redirect::to('/soppek/'.$idpro->id_proyek);
    }
}
