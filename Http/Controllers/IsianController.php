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

class IsianController extends Controller
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
        return view('detail.isian')->with('data', $data)->with('jenis',$jenis)->with('rab',$rab);
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
    
    public function downloadtemplate($id)
    {
        $jenis = DB::table('jenis_rab')->where('id_paket','=',$id)->get();
        $rab = DB::table('rab_paket as r')
            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
            ->select('r.*')
            ->where('j.id_paket','=',$id)->get();
        $jadwal = DB::table('jadwal as j')
            ->join('paket as p','j.id_paket','=','p.id_paket')
            ->where('p.id_paket','=',$id)->get();
        Excel::create('FORMULIR RAB '. strtoupper($jadwal[0]->nama_paket), function($excel) use ($jenis, $rab, $jadwal) {
            $excel->setTitle('FORMULIR RAB '.$jadwal[0]->nama_paket);
            $excel->setCreator('Dinas PU Gresik')->setCompany('Pemeritah Kabupaten Gresik');
            $excel->setDescription('Formulir RAB Paket Peritem');
            $excel->sheet('BQ', function($sheet) use ($jenis, $rab, $jadwal) {
                $sheet->setCellValue('A1', 'JENIS PEKERJAAN');
                $i=1;
                $cell_awal = 'B';
                $cell_akhir;
                foreach ($jadwal as $value) {
                    $sheet->setCellValue($cell_awal . '1', 'Minggu '.$value->ke_jadwal);
                    $cell_akhir = $cell_awal;
                    $cell_awal++;
                }
                $i=2;
                foreach ($jenis as $j) {
                    $sheet->setCellValue('A'.$i, $j->nama_jenis_rab);
                    $i+=1;
                    foreach ($rab as $r) {
                        if ($j->id_jenis_rab==$r->id_jenis_rab) {
                            $sheet->setCellValue('A'.$i, $r->pekerjaan_rab_paket);
                            $i+=1;
                        }                    
                    }
                }
            });
        })->export('xlsx');
    }
    public function UnggahJenis()
    {
        $idpaket = Input::get('idpaket1');
        $rules = array(
            'fileexcel1'      => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'            
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/isian/'.$idpaket)->withErrors($validator)->withInput();
        }else 
        {
            $rab = DB::table('rab_paket as r')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('j.id_paket','=',$idpaket)->get();
            foreach ($rab as $r) {
                DB::table('detail_rab')->where('id_rab_paket','=',$r->id_rab_paket)->delete();
            }
            $data = Excel::load(Input::file('fileexcel1'), function($reader) {})->get();
            if(!empty($data) && $data->count()){
                foreach ($data as $key => $value) {
                    if ($value->jenis_pekerjaan!='' && $value->jenis_pekerjaan!=null) {
                        $onok = DB::table('rab_paket as r')
                            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                            ->select(DB::raw('COUNT(*) as jml'))
                            ->where('j.id_paket','=',$idpaket)
                            ->where('r.pekerjaan_rab_paket','=',$value->jenis_pekerjaan)
                            ->first();
                        if ($onok->jml!=0) {
                            $jmljadwal = DB::table('jadwal')
                                ->select(DB::raw('MAX(ke_jadwal) as jml'))
                                ->where('id_paket','=',$idpaket)
                                ->first();
                            for ($i=1; $i <= $jmljadwal->jml; $i++) {
                                $idrab = DB::table('rab_paket')
                                    ->where('pekerjaan_rab_paket','=',$value->jenis_pekerjaan)
                                    ->first();
                                switch ($i) {
                                    case 1:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_1,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 2:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_2,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 3:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_3,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 4:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_4,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 5:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_5,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 6:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_6,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 7:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_7,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 8:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_8,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 9:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_9,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 10:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_10,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 11:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_11,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 12:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_12,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 13:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_13,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 14:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_14,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 15:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_15,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 16:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_16,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 17:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_17,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 18:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_18,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 19:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_19,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 20:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_20,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 21:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_21,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 22:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_22,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 23:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_23,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 24:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_24,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 25:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_25,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 26:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_26,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 27:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_27,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 28:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_28,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 29:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_29,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 30:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_30,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 31:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_31,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 31:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_31,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 32:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_32,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 33:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_33,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 34:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_34,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 35:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_35,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 36:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_36,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 37:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_37,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 38:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_38,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 39:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_39,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 40:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_40,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 41:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_41,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 42:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_42,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 43:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_43,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 44:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_44,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 45:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_45,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 46:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_46,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 47:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_47,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 48:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_48,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 49:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_49,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 50:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_50,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 51:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_51,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 52:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_52,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 53:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_53,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 54:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_54,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 55:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_55,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 56:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_56,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 57:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_57,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 58:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_58,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 59:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_59,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 60:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_60,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 61:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_61,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 62:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_62,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 63:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_63,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 64:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_64,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 65:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_65,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 66:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_66,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 67:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_67,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 68:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_68,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 69:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_69,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 70:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_70,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 71:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_71,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 72:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_72,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 73:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_73,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 74:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_74,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 75:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_75,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 76:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_76,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 77:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_77,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 78:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_78,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 79:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_79,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    case 80:
                                        DB::table('detail_rab')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rab'  => $value->minggu_80,
                                                'minggu_ke'       => $i
                                            ));
                                        break;
                                    default:     
                                } 
                            }
                        }     
                    }
                }
            }
            $file = Input::file('fileexcel1');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            if ($extension=='xls'||$extension=='xlsx') {
                $path1 = 'images/dokumentasi/'.$idpaket.'/rab/REALISASI_'.$idpaket.'.xls';
                if (File::exists($path1)) {
                        File::Delete($path1);
                }
                $path2 = 'images/dokumentasi/'.$idpaket.'/rab/REALISASI_'.$idpaket.'.xlsx';
                if (File::exists($path2)) {
                        File::Delete($path2);
                }
                Storage::disk('dokumentasi')->put($idpaket.'/rab/REALISASI_'.$idpaket.'.'.$extension,File::get($file));
                Session::flash('message', 'Data RAB Kontrak berhasil diunggah');
            }
            Session::flash('message', 'Data Form RAB berhasil diunggah');
            return Redirect::to('/isian/'.$idpaket);
        }
    }
    public function UnggahRencana()
    {
        $idpaket = Input::get('idpaket2');
        $rules = array(
            'fileexcel2'      => 'required'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'            
            );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails())
        {
            return Redirect::to('/isian/'.$idpaket)->withErrors($validator)->withInput();
        }else 
        {
            $rab = DB::table('rab_paket as r')
                ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                ->where('j.id_paket','=',$idpaket)->get();
            foreach ($rab as $r) {
                DB::table('detail_rencana')->where('id_rab_paket','=',$r->id_rab_paket)->delete();
            }
            $data = Excel::load(Input::file('fileexcel2'), function($reader) {})->get();
            if(!empty($data) && $data->count()){
                foreach ($data as $key => $value) {
                    if ($value->jenis_pekerjaan!='' && $value->jenis_pekerjaan!=null) {
                        $onok = DB::table('rab_paket as r')
                            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                            ->select(DB::raw('COUNT(*) as jml'))
                            ->where('j.id_paket','=',$idpaket)
                            ->where('r.pekerjaan_rab_paket','=',$value->jenis_pekerjaan)
                            ->first();
                        if ($onok->jml!=0) {
                            $jmljadwal = DB::table('jadwal')
                                ->select(DB::raw('MAX(ke_jadwal) as jml'))
                                ->where('id_paket','=',$idpaket)
                                ->first();
                            for ($i=1; $i <= $jmljadwal->jml; $i++) {
                                $idrab = DB::table('rab_paket')
                                    ->where('pekerjaan_rab_paket','=',$value->jenis_pekerjaan)
                                    ->first();
                                switch ($i) {
                                    case 1:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'         => $idrab->id_rab_paket,
                                                'isi_detail_rencana'    => $value->minggu_1,
                                                'minggu_ke_rencana'     => $i
                                            ));
                                        break;
                                    case 2:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_2,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 3:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_3,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 4:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_4,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 5:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_5,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 6:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_6,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 7:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_7,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 8:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_8,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 9:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_9,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 10:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_10,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 11:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_11,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 12:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_12,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 13:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_13,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 14:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_14,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 15:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_15,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 16:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_16,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 17:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_17,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 18:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_18,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 19:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_19,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 20:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_20,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 21:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_21,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 22:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_22,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 23:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_23,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 24:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_24,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 25:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_25,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 26:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_26,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 27:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_27,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 28:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_28,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 29:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_29,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 30:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_30,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 31:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_31,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 32:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_32,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 33:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_33,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 34:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_34,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 35:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_35,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 36:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_36,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 37:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_37,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 38:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_38,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 39:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_39,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 40:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_40,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 41:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_41,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 42:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_42,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 43:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_43,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 44:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_44,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 45:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_45,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 46:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_46,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 47:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_47,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 48:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_48,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 49:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_49,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 50:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_50,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 51:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_51,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 52:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_52,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 53:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_53,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 54:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_54,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 55:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_55,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 56:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_56,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 57:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_57,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 58:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_58,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 59:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_59,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 60:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_60,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 61:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_61,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 62:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_62,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 63:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_63,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 64:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_64,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 65:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_65,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 66:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_66,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 67:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_67,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 68:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_68,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 69:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_69,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 70:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_70,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 71:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_71,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 72:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_72,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 73:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_73,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 74:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_74,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 75:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_75,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 76:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_76,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 77:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_77,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 78:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_78,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 79:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_79,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    case 80:
                                        DB::table('detail_rencana')->insert(array(
                                                'id_rab_paket'   => $idrab->id_rab_paket,
                                                'isi_detail_rencana'  => $value->minggu_80,
                                                'minggu_ke_rencana'       => $i
                                            ));
                                        break;
                                    default:     
                                } 
                            }
                        }     
                    }
                }
            }
            Session::flash('message', 'Data Form RAB berhasil diunggah');
            return Redirect::to('/isian/'.$idpaket);
        }
    }
}
