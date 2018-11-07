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

class JadwalPengisianCont extends Controller
{
    public function index(){
    	$data =DB::table('jadwal')->first();
    	return view ('jadwal.jadwal_list')->with('data',$data);
    }
    public function update()
    {
        $id = Input::get('jadwalid');
        $tutup = Input::get('tutup');
        if ($tutup==0) {
        	DB::table('jadwal')->where('id_jadwal',$id)->update(
	        array(   
	            'tutup'   => 1
	        ));
        } else {
        	DB::table('jadwal')->where('id_jadwal',$id)->update(
	        array(   
	            'tutup'   => 0
	        ));
        }
 
		Session::flash('message', 'Data Program Berhasil Diubah !');
		
	    return Redirect::to('/jadwal');     
    }
}
