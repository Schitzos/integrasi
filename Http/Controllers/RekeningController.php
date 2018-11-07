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
use \Symfony\Component\HttpFoundation\File\UploadedFile;
use Validator;
use Schema;
use Input;
use Session;
use Redirect;
use View;
use Hash;
use Auth;
use Response;

class RekeningController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $data = DB::table('rekening as a')
            ->join('tipe_paket   as b','a.id_tipe_paket','=','b.id_tipe_paket')
            ->orderBy('nomor_rekening', 'asc')
            ->get();
        return view('rekening.index')->with('data',$data);
    }

    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('rekening as a')
                ->join('tipe_paket   as b','a.id_tipe_paket','=','b.id_tipe_paket')
                ->where('id_rekening','=',$id)
                ->first();
            return Response::json($data);
        }
    }

    public function store()
    {
        $rules = array(
            'nomor_rekening' => 'required',
            'tindakan' => 'required',
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {   
            return Redirect::to('/rekening')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('rekening')->insert(
                array(   
                    'nomor_rekening'    => Input::get('nomor_rekening'),
                    'id_tipe_paket'    => Input::get('tindakan')
                ));
        Session::flash('message', 'Data Tindakan Rekening berhasil ditambahkan');
        return Redirect::to('/rekening');
        }
    }
    public function update()
    {
        $rules = array(
            'nomor_rekening' => 'required',
            'tindakan' => 'required',
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/rekening')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('id_rekening');
            DB::table('rekening')->where('id_rekening',$id)->update(
                array(   
                    'nomor_rekening'    => Input::get('nomor_rekening'),
                    'id_tipe_paket'    => Input::get('tindakan')
                )
            );
            Session::flash('message', 'Data Rekening Tindakan berhasil diubah');
            return Redirect::to('/rekening');
        }       
    }
    public function destroy($id)
    {
        DB::table('rekening')->where('id_Rekening', '=',$id)->delete();
        Session::flash('message', 'Data Rekening Tindakan berhasil dihapus !');
        return Redirect::to('/rekening');
    }

    public function getTindakan(){
        $data = DB::table('tipe_paket')
        ->orderBy('nama_tipe', 'asc')
        ->get();
        return Response::json($data);
    }
}
