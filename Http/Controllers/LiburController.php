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

class LiburController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $data = DB::table('libur')
            ->get();
        return view('libur.index')->with('data',$data);
    }

    public function showdata(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('libur')
                ->where('id_libur','=',$id)
                ->first();
            return Response::json($data);
        }
    }

    public function store()
    {
        $rules = array(
            'Tgl_Libur' => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/libur')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('libur')->insert(
                array(   
                    'tgl_libur'         => date('Y-m-d',strtotime(Input::get('Tgl_Libur'))),
                    'keterangan_libur'  => Input::get('Keterangan_Libur')
                ));
        Session::flash('message', 'Data Hari Libur berhasil ditambahkan');
        return Redirect::to('/libur');
        }
    }
    public function update()
    {
        $rules = array(
            'Tgl_Libur_Ubah'        => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/libur')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('idlibur');
            DB::table('libur')->where('id_libur',$id)->update(
                array(   
                    'tgl_libur'         => date('Y-m-d',strtotime(Input::get('Tgl_Libur_Ubah'))),
                    'keterangan_libur'  => Input::get('Keterangan_Libur_Ubah')
                )
            );
            Session::flash('message', 'Data Hari Libur berhasil diubah');
            return Redirect::to('/libur');
        }       
    }
    public function destroy($id)
    {
        DB::table('libur')->where('id_libur', '=',$id)->delete();
        Session::flash('message', 'Data Hari Libur berhasil dihapus !');
        return Redirect::to('/libur');
    }
}
