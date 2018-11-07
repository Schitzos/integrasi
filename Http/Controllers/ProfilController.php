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


class ProfilController extends Controller
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
        $profil =DB::table('admin')->where('id','=',Auth::user()->id)->first();
        return view('profil')->with('data', $data)->with('profil', $profil);
    }
    public function master()
    {
        $profil =DB::table('admin')->where('id','=',Auth::user()->id)->first();
        return view('profilmaster')->with('profil', $profil);
    }
    
    public function update()
    {
        $idpro = Input::get('idpro');
        $iduser = Input::get('iduser');
        $rules = array(
                  'username' => 'required|max:255',
                  'email' => 'required|email|max:255',
                  'password' => 'required|confirmed|min:6',
                  'gambar1' => 'mimes:jpeg,jpg,png,gif'
            );
        $messages = array(
                  'required' => 'Kolom :attribute harus di isi.',
                  'max' => 'Panjang :attribute tidak boleh melebihi :max karakter.',
                  'min' => 'Panjang :attribute minimal :min karakter.',
                  'integer' => 'Kolom :attribute harus dipilih.',
                  'email' => 'Masukkan email yang valid.',
                  'confirmed' => 'Password konfirmasi tidak sama',
                  'mimes'=>'Pilih File Gambar (*.jpeg,*.jpg,*.png,*.gif)'
            );
            $validator = Validator::make(Input::all(), $rules,$messages);
            if ($validator->fails()) {   
                  return Redirect::to('/profil/'.$idpro)->withErrors($validator)->withInput();
            } else {
            if (Input::file('gambar1') != null) {
                    $file = Input::file('gambar1');
                    $extension = $file->getClientOriginalExtension();
                    Storage::disk('gambar')->put($file->getFilename().'.'.$extension,  File::get($file));
                        $filegambar = $file->getFilename().'.'.$extension;

                    DB::table('admin')
                            ->where('id',$iduser)
                            ->update(
                                array(
                                'username' => Input::get('username'),
                                'email' => Input::get('email'),
                                'remember_token' => Input::get('_token'),
                                'password' => Hash::make(Input::get('password')),
                                'andropass' => MD5(Input::get('password')),
                                'avatar' => $filegambar
                            )
                        );
            }else{
                DB::table('admin')
                    ->where('id',$iduser)
                    ->update(
                        array(
                        'username' => Input::get('username'),
                        'email' => Input::get('email'),
                        'remember_token' => Input::get('_token'),
                        'password' => Hash::make(Input::get('password')),
                        'andropass' => MD5(Input::get('password'))
                        )
                    );
            }
            $tglsekarang = date('Y-m-d H:i:s');
            $name = Input::get('name');
            Session::flash('message', 'Data profil Anda berhasil diubah');
            return Redirect::to('/profil/'.$idpro);
            }
    }
    public function updatemaster()
    {
        $iduser = Input::get('iduser');
        $rules = array(
                  'username' => 'required|max:255',
                  'email' => 'required|email|max:255',
                  'password' => 'required|confirmed|min:6',
                  'gambar1' => 'mimes:jpeg,jpg,png,gif'
            );
        $messages = array(
                  'required' => 'Kolom :attribute harus di isi.',
                  'max' => 'Panjang :attribute tidak boleh melebihi :max karakter.',
                  'min' => 'Panjang :attribute minimal :min karakter.',
                  'integer' => 'Kolom :attribute harus dipilih.',
                  'email' => 'Masukkan email yang valid.',
                  'confirmed' => 'Password konfirmasi tidak sama',
                  'mimes'=>'Pilih File Gambar (*.jpeg,*.jpg,*.png,*.gif)'
            );
            $validator = Validator::make(Input::all(), $rules,$messages);
            if ($validator->fails()) {   
                  return Redirect::to('/profilmaster')->withErrors($validator)->withInput();
            } else {
            if (Input::file('gambar1') != null) {
                    $file = Input::file('gambar1');
                    $extension = $file->getClientOriginalExtension();
                    Storage::disk('gambar')->put($file->getFilename().'.'.$extension,  File::get($file));
                        $filegambar = $file->getFilename().'.'.$extension;

                    DB::table('admin')
                            ->where('id',$iduser)
                            ->update(
                                array(
                                'username' => Input::get('username'),
                                'email' => Input::get('email'),
                                'remember_token' => Input::get('_token'),
                                'password' => Hash::make(Input::get('password')),
                                'andropass' => MD5(Input::get('password')),
                                'avatar' => $filegambar
                            )
                        );
            }else{
                DB::table('admin')
                    ->where('id',$iduser)
                    ->update(
                        array(
                        'username' => Input::get('username'),
                        'email' => Input::get('email'),
                        'remember_token' => Input::get('_token'),
                        'password' => Hash::make(Input::get('password')),
                        'andropass' => MD5(Input::get('password'))
                        )
                    );
            }
            $tglsekarang = date('Y-m-d H:i:s');
            $name = Input::get('name');
            Session::flash('message', 'Data profil Anda berhasil diubah');
            return Redirect::to('/profilmaster');
            }
    }
}
