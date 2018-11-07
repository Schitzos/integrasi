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

class RABController extends Controller
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
        $jenis = DB::table('jenis_rab')->where('id_paket','=',$id)->get();
        $rab = DB::table('rab_paket')->get();
        return view('detail.rab')->with('data', $data)->with('jenis',$jenis)->with('rab',$rab);
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
    public function UnggahRAB()
    {
        $idpaket = Input::get('idpaket');
        $rules = array(
            'fileexcel' => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'            
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/rab/'.$idpaket)->withErrors($validator)->withInput();
        }else 
        {
            $jenis = DB::table('jenis_rab')->where('id_paket','=',$idpaket)->get();
            foreach ($jenis as $j) {
                DB::table('rab_paket')->where('id_jenis_rab','=',$j->id_jenis_rab)->delete();
            }
            DB::table('jenis_rab')->where('id_paket','=',$idpaket)->delete();
            $data = Excel::load(Input::file('fileexcel'), function($reader) {})->get();
            if(!empty($data) && $data->count()){
                foreach ($data as $key => $value) {
                    if ($value->jenis!='' && $value->jenis!=null) {
                        $onok = DB::table('jenis_rab')
                            ->select(DB::raw('COUNT(*) as jml'))
                            ->where('id_paket','=',$idpaket)
                            ->where('nama_jenis_rab','=',$value->jenis)
                            ->first();
                        if ($onok->jml==0) {
                            DB::table('jenis_rab')->insert(
                            array(
                                'id_paket'           => $idpaket,
                                'nama_jenis_rab'      => $value->jenis
                            ));
                            $jenis = DB::table('jenis_rab')->select(DB::raw('MAX(id_jenis_rab) as id_jenis_rab'))
                                ->first();
                            DB::table('rab_paket')->insert(
                            array(
                                'id_jenis_rab'          => $jenis->id_jenis_rab,
                                'pekerjaan_rab_paket'  => $value->pekerjaan,
                                'satuan_rab_paket'     => $value->satuan,
                                'volume_rab_paket'     => $value->volume,
                                'harga_rab_paket'      => $value->harga
                            ));
                        } else {
                            $jenis = DB::table('jenis_rab')
                                ->where('id_paket','=',$idpaket)
                                ->where('nama_jenis_rab','=',$value->jenis)
                                ->first();
                            DB::table('rab_paket')->insert(
                            array(
                                'id_jenis_rab'          => $jenis->id_jenis_rab,
                                'pekerjaan_rab_paket'  => $value->pekerjaan,
                                'satuan_rab_paket'     => $value->satuan,
                                'volume_rab_paket'     => $value->volume,
                                'harga_rab_paket'      => $value->harga
                            ));
                        }     
                    }
                }
            }
            Session::flash('message', 'Data RAB berhasil diunggah');
            return Redirect::to('/rab/'.$idpaket);
        }
    }
    public function UploadRAB()
    {
        $idpaket = Input::get('idpaket0');
        $rules = array(
            'fileexcel0' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/rab/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('fileexcel0');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/rab/RABKONTRAK_'.$idpaket.'.xls';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/rab/RABKONTRAK_'.$idpaket.'.xlsx';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/rab/RABKONTRAK_'.$idpaket.'.'.$extension,File::get($file));
                Session::flash('message', 'Data RAB Kontrak berhasil diunggah');
                return Redirect::to('/rab/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe Excel (*xls,*xlsx) !!');
                return Redirect::to('/rab/'.$idpaket);
            }
        }
    }
}
