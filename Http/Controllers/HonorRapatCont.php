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
use Auth;
use \PDF;
use App;
use Response;

class HonorRapatCont extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
    }
    
    public function index()
	{   
        $data = DB::table('honor_rapat')->get();
        return view('honorrapat.index')->with('data',$data);
    }

    public function store()
    {
        $rules = array(
            'Nama_Bidang'   => 'required'
            );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/bidang')->withErrors($validator)->withInput();
        }else 
        {
            DB::table('bidang')->insert(
                array(   
                    'nama_bidang'   => Input::get('Nama_Bidang')
                ));
        Session::flash('message', 'Data Bidang berhasil ditambahkan');
        return Redirect::to('/honorrapat');
        }
    }
}
