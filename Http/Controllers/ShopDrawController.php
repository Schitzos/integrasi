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


class ShopDrawController extends Controller
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
        $shopdraw = DB::table('shop_draw as s')
                ->join('paket as p','s.id_paket','=','p.id_paket')
                ->where('p.id_paket','=',$id)
                ->get();
        return view('detail.shopdraw')->with('data', $data)->with('shopdraw',$shopdraw);
    }

    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('shop_draw as s')
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
            $shopdraw = DB::table('shop_draw as s')
                ->join('paket as p','s.id_paket','=','p.id_paket')
                ->where('p.id_paket','=',$id)
                ->get();
            return view('include.tabelshopdraw')->with('shopdraw',$shopdraw); 
        }
    }
    public function simpan()
    {
        DB::table('shop_draw')->insert(
            array(   
                'id_paket'             => Input::get('id'),
                'uraian_shop_draw'      => Input::get('uraian'),
                'status_shop_draw'      => Input::get('statuse'),
                'pelaksanaan_shop_draw' => Input::get('pelaksana')
            )
        );
    }
    public function ubah()
    {
        DB::table('shop_draw')->where('id_shop_draw','=',Input::get('idshop'))->update(
            array(   
                'id_paket'             => Input::get('id'),
                'uraian_shop_draw'      => Input::get('uraian'),
                'status_shop_draw'      => Input::get('statuse'),
                'pelaksanaan_shop_draw' => Input::get('pelaksana')
            )
        );
    }

    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $idked = Input::get('idshop');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/shopdraw/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='pdf'||$extension=='PDF') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/rks/GAMBAR_KERJA_'.$idpaket.'_'.$idked.'.pdf';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/rks/GAMBAR_KERJA_'.$idpaket.'_'.$idked.'.PDF';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/rks/GAMBAR_KERJA_'.$idpaket.'_'.$idked.'.'.$extension,File::get($file));
                Session::flash('message', 'Data Gambar Kerja berhasil diunggah');
                return Redirect::to('/shopdraw/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/shopdraw/'.$idpaket);
            }
        }
    }
}
