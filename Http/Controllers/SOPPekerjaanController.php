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


class SOPPekerjaanController extends Controller
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
        $soppekerjaan  = DB::table('sop_pekerjaan as s')->join('paket as p','s.id_paket','=','p.id_paket')
                ->where('s.id_paket','=',$id)->get();
        return view('detail.sop')->with('data', $data)->with('soppekerjaan',$soppekerjaan);
    }
    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('sop_pekerjaan as s')
                ->join('paket as p','s.id_paket','=','p.id_paket')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('p.id_paket','=',$id)
                ->first();
            return Response::json($data);
        }
    }
    public function showdetail(Request $request, $id)
    {
        if ($request->ajax()) {
            $soppekerjaan = DB::table('sop_pekerjaan as s')
                ->join('paket as p','s.id_paket','=','p.id_paket')
                ->where('p.id_paket','=',$id)
                ->get();
            return view('include.tabelsoppekerjaan')->with('soppekerjaan',$soppekerjaan); 
        }
    }
    public function simpan()
    {
        DB::table('sop_pekerjaan')->insert(
            array(   
                'id_paket'             => Input::get('id'),
                'uraian_sop_pekerjaan'   => Input::get('uraian'),
                'persetujuan_spo_pekerjaan'   => Input::get('status'),
                'no_shop_sop_pekerjaan'   => Input::get('noshop'),
                'status_shop_sop_pekerjaan'   => Input::get('statusesop'),
                'no_izin_sop_pekerjaan'   => Input::get('uraian'),
                'status_izin_sop_pekerjaan'   => Input::get('statuseizin'),
                'status_sop_pekerjaan'   => Input::get('onstatus'),
            )
        );
    }
    public function ubah()
    {
        DB::table('sop_pekerjaan')->where('id_sop_pekerjaan','=',Input::get('idshop'))->update(
            array(   
                'id_paket'             => Input::get('id'),
                'uraian_sop_pekerjaan'   => Input::get('uraian'),
                'persetujuan_spo_pekerjaan'   => Input::get('status'),
                'no_shop_sop_pekerjaan'   => Input::get('noshop'),
                'status_shop_sop_pekerjaan'   => Input::get('statusesop'),
                'no_izin_sop_pekerjaan'   => Input::get('uraian'),
                'status_izin_sop_pekerjaan'   => Input::get('statuseizin'),
                'status_sop_pekerjaan'   => Input::get('onstatus'),
            )
        );
    }

    public function uploadpdf()
    {
        $idpaket = Input::get('idpaket');
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
            return Redirect::to('/soppek/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('gambar1');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                Storage::disk('dokumentasi')->put($idpaket.'/pelaksanaan/SOPPEKERJAAN_'.$idpaket.'.'.$extension,File::get($file));
                DB::table('sop')->insert(
                array( 
                    'id_paket'         => $idpaket,
                    'nama_file_sop'     => Input::get('Nama_File'),
                    'lokasi_sop'        => $filenya
                ));
                Session::flash('message', 'Data SOP Pekerjaan berhasil ditambahkan');
                return Redirect::to('/soppek/'.$idpaket);
            } elseif ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/pelaksanaan/SOPPEKERJAAN_'.$idpaket.'.'.$extension,File::get($file));
                DB::table('sop')->insert(
                array( 
                    'id_paket'        => $idpaket,
                    'nama_file_sop'    => Input::get('Nama_File'),
                    'lokasi_sop'       => $filenya
                ));
                Session::flash('message', 'Data SOP Pekerjaan berhasil ditambahkan');
                return Redirect::to('/soppek/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe pdf atau excel (*.pdf,*xls,*xlsx) !!');
                return Redirect::to('/soppek/'.$idpaket);
            }
        }
    }
}
