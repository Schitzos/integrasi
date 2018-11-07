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

class LapHarianController extends Controller
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
        $jadwal = DB::table('jadwal')->where('id_paket','=',$id)->get();
        $laporan = DB::table('lapharian')->where('id_paket','=',$id)->get();
        return view('detail.lapharian')->with('data', $data)->with('jadwal',$jadwal)->with('laporan',$laporan);
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
    public function UploadPDF()
    {
        $idpaket = Input::get('idpaket');
        $rules = array(
            'filepdf' => 'required'
        );      
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.'
        );
      $validator = Validator::make(Input::all(), $rules,$messages);
      if ($validator->fails())
      {   
            return Redirect::to('/lapharian/'.$idpaket)->withErrors($validator)->withInput();
      }else
      {
            $file = Input::file('filepdf');
            $extension = $file->getClientOriginalExtension();
            $mimetype = $file->getClientMimeType();
            $filenya = $file->getFilename().'.'.$extension;
            $jmllaporan = DB::table('lapharian')
                ->select(DB::raw('COUNT(*) AS jml'))
                ->where('id_paket','=',$idpaket)
                ->first();
            $nomorlaporan = $jmllaporan->jml + 1;
            $namalaporan = 'LAPHARIAN_'.$idpaket.'_'.$nomorlaporan.'.'.$extension;
            DB::table('lapharian')->insert(
                array(   
                    'id_paket'              => $idpaket,
                    'nama_lapharian'         => Input::get('Nama_Laporan'),
                    'lokasi_lapharian'       => $namalaporan,
                    'tgl_uplod_lapharian'    => date('Y-m-d')
                )
            );
            if ($extension=='pdf'||$extension=='PDF') {
                Storage::disk('dokumentasi')->put($idpaket.'/laporan/LAPHARIAN_'.$idpaket.'_'.$nomorlaporan.'.'.$extension,File::get($file));
                Session::flash('message', 'Data Laporan Harian berhasil diunggah');
                return Redirect::to('/lapharian/'.$idpaket);
            } else {
                Session::flash('eror', 'Pilih File Bertipe PDF (*.pdf,*.PDF) !!');
                return Redirect::to('/lapharian/'.$idpaket);
            }
        }
    }
    public function unduh($id)
    {
        $paket = DB::table('paket as p')
            ->join('kegiatan as k','p.id_kegiatan','=','k.id_kegiatan')
            ->join('program as r','k.id_program','=','r.id_program')
            ->leftjoin('kontraktor as t','p.id_kontraktor','=','t.id_kontraktor')
            ->leftjoin('konsultan as s','p.id_konsultan','=','s.id_konsultan')
            ->leftjoin('desa as d','p.id_desa','=','d.id_desa')
            ->where('p.id_paket','=',$id)
            ->first();
        Excel::create('LAP HARIAN '. strtoupper($paket->nama_paket), function($excel) use ($paket) {
            $excel->setTitle('LAP HARIAN '.$paket->nama_paket);
            $excel->setCreator('Dinas PU Gresik')->setCompany('Pemeritah Kabupaten Gresik');
            $excel->setDescription('Formulir Laporan Harian');
            $jadwal = DB::table('jadwal')->where('id_paket','=',$paket->id_paket)->get();
            $lanjutnya="";
            foreach ($jadwal as $value) {
                $excel->sheet('REKAP TENAGA ('.$value->ke_jadwal.')', function($sheet) use ($paket, $value,$lanjutnya) {
                    $sheet->mergeCells('A1:F1');
                    $sheet->mergeCells('A2:F2');
                    $sheet->mergeCells('G1:J2');
                    $sheet->setCellValue('A1', 'DINAS PEKERJAAN PEKERJAAN UMUM DAN TATA RUANG');
                    $sheet->setCellValue('A2', 'PEMERINTAH KABUPATEN GRESIK');
                    $sheet->setCellValue('G1', 'LAPORAN TENAGA KERJA');
                    $tglmulai ="";
                    $hari1="";
                    $tglselesai="";
                    $hari2="";
                    if ($value->ke_jadwal==1) {
                        $dtmulai = new Carbon($paket->tgl_awal_kontrak);
                        $dtselesai = new Carbon($value->minggu_jadwal);
                        $dtselesai = $dtselesai->subDays(1);
                        $hari1 = $dtmulai->format('l');
                        switch ($hari1) {
                            case 'Sunday':
                                $hari1 = "MINGGU";
                                break;
                            case 'Monday':
                                $hari1 = "SENIN";
                                break;
                            case 'Tuesday':
                                $hari1 = "SELASA";
                                break;
                            case 'Wednesday':
                                $hari1 = "RABU";
                                break;
                            case 'Thursday':
                                $hari1 = "KAMIS";
                                break;
                            case 'Friday':
                                $hari1 = "JUMAT";
                                break;
                            case 'Saturday':
                                $hari1 = "SABTU";
                                break;
                            default:
                        }
                        $hari2 = $dtselesai->format('l');
                        switch ($hari2) {
                            case 'Sunday':
                                $hari2 = "MINGGU";
                                break;
                            case 'Monday':
                                $hari2 = "SENIN";
                                break;
                            case 'Tuesday':
                                $hari2 = "SELASA";
                                break;
                            case 'Wednesday':
                                $hari2 = "RABU";
                                break;
                            case 'Thursday':
                                $hari2 = "KAMIS";
                                break;
                            case 'Friday':
                                $hari2 = "JUMAT";
                                break;
                            case 'Saturday':
                                $hari2 = "SABTU";
                                break;
                            default:
                        }
                        if (date("m",strtotime($paket->tgl_awal_kontrak))==1) {
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Januari ';
                        } elseif (date("m",strtotime($paket->tgl_awal_kontrak))==2){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Februari ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==3){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Maret ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==4){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' April ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==5){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Mei ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==6){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Juni ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==7){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Juli ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==8){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Agustus ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==9){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' September ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==10){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Oktober ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==11){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' November ';
                        }elseif (date("m",strtotime($paket->tgl_awal_kontrak))==12){
                            $tglmulai = date('d',strtotime($paket->tgl_awal_kontrak)).' Desember ';
                        }
                        if (date("m",strtotime($value->minggu_jadwal))==1) {
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Januari '.date('Y',strtotime($value->minggu_jadwal));
                        } elseif (date("m",strtotime($value->minggu_jadwal))==2){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Februari '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==3){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Maret '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==4){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' April '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==5){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Mei '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==6){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Juni '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==7){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Juli '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==8){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Agustus '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==9){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' September '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==10){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Oktober '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==11){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' November '.date('Y',strtotime($value->minggu_jadwal));
                        }elseif (date("m",strtotime($value->minggu_jadwal))==12){
                            $tglselesai = date('d',strtotime($value->minggu_jadwal)).' Desember '.date('Y',strtotime($value->minggu_jadwal));
                        }
                    } else {
                        $dtmulai = new Carbon($lanjutnya);
                        $dtselesai = new Carbon($value->minggu_jadwal);
                        $dtselesai = $dtselesai->subDays(1);
                        $hari1 = $dtmulai->format('l');
                        switch ($hari1) {
                            case 'Sunday':
                                $hari1 = "MINGGU";
                                break;
                            case 'Monday':
                                $hari1 = "SENIN";
                                break;
                            case 'Tuesday':
                                $hari1 = "SELASA";
                                break;
                            case 'Wednesday':
                                $hari1 = "RABU";
                                break;
                            case 'Thursday':
                                $hari1 = "KAMIS";
                                break;
                            case 'Friday':
                                $hari1 = "JUMAT";
                                break;
                            case 'Saturday':
                                $hari1 = "SABTU";
                                break;
                            default:
                        }
                        $hari2 = $dtselesai->format('l');
                        switch ($hari2) {
                            case 'Sunday':
                                $hari2 = "MINGGU";
                                break;
                            case 'Monday':
                                $hari2 = "SENIN";
                                break;
                            case 'Tuesday':
                                $hari2 = "SELASA";
                                break;
                            case 'Wednesday':
                                $hari2 = "RABU";
                                break;
                            case 'Thursday':
                                $hari2 = "KAMIS";
                                break;
                            case 'Friday':
                                $hari2 = "JUMAT";
                                break;
                            case 'Saturday':
                                $hari2 = "SABTU";
                                break;
                            default:
                        }
                        if (date("m",strtotime($value->minggu_jadwal))==1) {
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Januari ';
                        } elseif (date("m",strtotime($value->minggu_jadwal))==2){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Februari ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==3){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Maret ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==4){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' April ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==5){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Mei ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==6){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Juni ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==7){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Juli ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==8){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Agustus ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==9){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' September ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==10){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Oktober ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==11){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' November ';
                        }elseif (date("m",strtotime($value->minggu_jadwal))==12){
                            $tglmulai = date('d',strtotime($value->minggu_jadwal)).' Desember ';
                        }
                        if (date("m",strtotime($dtselesai))==1) {
                            $tglselesai = date('d',strtotime($dtselesai)).' Januari '.date('Y',strtotime($dtselesai));
                        } elseif (date("m",strtotime($dtselesai))==2){
                            $tglselesai = date('d',strtotime($dtselesai)).' Februari '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==3){
                            $tglselesai = date('d',strtotime($dtselesai)).' Maret '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==4){
                            $tglselesai = date('d',strtotime($dtselesai)).' April '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==5){
                            $tglselesai = date('d',strtotime($dtselesai)).' Mei '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==6){
                            $tglselesai = date('d',strtotime($dtselesai)).' Juni '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==7){
                            $tglselesai = date('d',strtotime($dtselesai)).' Juli '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==8){
                            $tglselesai = date('d',strtotime($dtselesai)).' Agustus '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==9){
                            $tglselesai = date('d',strtotime($dtselesai)).' September '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==10){
                            $tglselesai = date('d',strtotime($dtselesai)).' Oktober '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==11){
                            $tglselesai = date('d',strtotime($dtselesai)).' November '.date('Y',strtotime($dtselesai));
                        }elseif (date("m",strtotime($dtselesai))==12){
                            $tglselesai = date('d',strtotime($dtselesai)).' Desember '.date('Y',strtotime($dtselesai));
                        }
                    }
                    $sheet->mergeCells('C3:J3');
                    $sheet->mergeCells('C4:J4');
                    $sheet->mergeCells('C5:J5');
                    $sheet->mergeCells('C6:J6');
                    $sheet->mergeCells('C7:J7');
                    $sheet->setCellValue('A3', 'MINGGU KE');
                    $sheet->setCellValue('B3', ':');
                    $sheet->setCellValue('C3', $value->ke_jadwal);
                    $sheet->setCellValue('A4', 'HARI/TANGGAL');
                    $sheet->setCellValue('B4', ':');
                    $sheet->setCellValue('C4', $hari1.' '.$tglmulai.' s/d '.$hari2.' '.$tglselesai);
                    $sheet->setCellValue('A5', 'NAMA KEGIATAN');
                    $sheet->setCellValue('B5', ':');
                    $sheet->setCellValue('C5', $paket->nama_kegiatan);
                    $sheet->setCellValue('A6', 'NAMA PEKERJAAN');
                    $sheet->setCellValue('B6', ':');
                    $sheet->setCellValue('C6', $paket->nama_paket);
                    $sheet->setCellValue('A6', 'LOKASI PROYEK');
                    $sheet->setCellValue('B6', ':');
                    $sheet->setCellValue('C6', $paket->nama_desa);
                    $sheet->setCellValue('A7', 'TAHUN ANGGARAN');
                    $sheet->setCellValue('B7', ':');
                    $sheet->setCellValue('C7', $paket->tahun_anggaran);
                    $sheet->cell('A1', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                    });
                    $sheet->cell('A2', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                    });
                    $sheet->cell('G1', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    // $sheet->cell('A3:J7', function($cell){
                    //     $cell->setBorder('thin','thin','thin','thin');
                    // });
                    $sheet->mergeCells('A8:C9');
                    $sheet->setCellValue('A8', 'URAIAN');
                    $sheet->cell('A8', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->mergeCells('D8:J8');
                    $sheet->setCellValue('D8', 'JUMLAH TENAGA KERJA');
                    $sheet->cell('D8', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('D9', 'SENIN');
                    $sheet->cell('D9', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('E9', 'SELASA');
                    $sheet->cell('E9', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('F9', 'RABU');
                    $sheet->cell('F9', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('G9', 'KAMIS');
                    $sheet->cell('G9', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('H9', 'JUMAT');
                    $sheet->cell('H9', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('I9', 'SABTU');
                    $sheet->cell('I9', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('J9', 'MINGGU');
                    $sheet->cell('J9', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setValignment('center');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $baris=10;
                    for ($i=10; $i < 26 ; $i++) { 
                        $sheet->mergeCells('A'.$baris.':C'.$baris);
                        $sheet->cell('A'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $sheet->cell('D'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $sheet->cell('E'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $sheet->cell('F'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $sheet->cell('G'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $sheet->cell('H'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $sheet->cell('I'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $sheet->cell('J'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                        });
                        $baris++;
                    }
                    $sheet->mergeCells('A27:E27');
                    $sheet->mergeCells('F27:J27');
                    $sheet->setCellValue('A27', 'Diketahui');
                    $sheet->cell('A27', function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->setCellValue('F27', 'Dibuat / diusulkan Oleh :');
                    $sheet->cell('F27', function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('A28:E28');
                    $sheet->mergeCells('F28:J28');
                    $sheet->setCellValue('A28', 'Konsultan Pengawas');
                    $sheet->cell('A28', function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->setCellValue('F28', 'Penyedian Barang/Jasa');
                    $sheet->cell('F28', function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('A29:E29');
                    $sheet->mergeCells('F29:J29');
                    $sheet->setCellValue('A29', strtoupper($paket->nama_konsultan));
                    $sheet->cell('A29', function($cell){
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('F29', strtoupper($paket->nama_kontraktor));
                    $sheet->cell('F29', function($cell){
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->mergeCells('A35:E35');
                    $sheet->mergeCells('F35:J35');
                    $sheet->setCellValue('A35', strtoupper($paket->direktur_konsultan));
                    $sheet->cell('A35', function($cell){
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('F35', strtoupper($paket->direktur_kontraktor));
                    $sheet->cell('F35', function($cell){
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->mergeCells('A36:E36');
                    $sheet->mergeCells('F36:J36');
                    $sheet->setCellValue('A36', 'Pengawas');
                    $sheet->cell('A36', function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->setCellValue('F36', 'Pelaksana');
                    $sheet->cell('F36', function($cell){
                        $cell->setAlignment('center');
                    });
                });
                $lanjutnya = $value->minggu_jadwal;
                if ($value->ke_jadwal==1) {
                    $dtmulai = new Carbon($paket->tgl_awal_kontrak);
                    $dtselesai = new Carbon($value->minggu_jadwal);
                    $dtselesai = $dtselesai->subDays(1);
                    $jmlhari = $dtmulai->diffInDays($dtselesai, false);
                    for ($i=0; $i <=$jmlhari ; $i++) {
                        $hari = $dtmulai->format('l');
                        switch ($hari) {
                            case 'Sunday':
                                $hari = "MINGGU";
                                break;
                            case 'Monday':
                                $hari = "SENIN";
                                break;
                            case 'Tuesday':
                                $hari = "SELASA";
                                break;
                            case 'Wednesday':
                                $hari = "RABU";
                                break;
                            case 'Thursday':
                                $hari = "KAMIS";
                                break;
                            case 'Friday':
                                $hari = "JUMAT";
                                break;
                            case 'Saturday':
                                $hari = "SABTU";
                                break;
                            default:
                        }
                        $excel->sheet($hari.'('.$value->ke_jadwal.')', function($sheet) use ($paket, $value, $dtmulai, $hari) {
                            $sheet->mergeCells('A1:F1');
                            $sheet->mergeCells('A2:F2');
                            $sheet->mergeCells('G1:L2');
                            $sheet->setCellValue('A1', 'DINAS PEKERJAAN PEKERJAAN UMUM DAN TATA RUANG');
                            $sheet->setCellValue('A2', 'PEMERINTAH KABUPATEN GRESIK');
                            $sheet->setCellValue('G1', 'LAPORAN HARIAN');
                            $tgl ="";
                            if (date("m",strtotime($dtmulai))==1) {
                                $tgl = date('d',strtotime($dtmulai)).' Januari '.date('Y',strtotime($dtmulai));
                            } elseif (date("m",strtotime($dtmulai))==2){
                                $tgl = date('d',strtotime($dtmulai)).' Februari '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==3){
                                $tgl = date('d',strtotime($dtmulai)).' Maret '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==4){
                                $tgl = date('d',strtotime($dtmulai)).' April '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==5){
                                $tgl = date('d',strtotime($dtmulai)).' Mei '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==6){
                                $tgl = date('d',strtotime($dtmulai)).' Juni '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==7){
                                $tgl = date('d',strtotime($dtmulai)).' Juli '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==8){
                                $tgl = date('d',strtotime($dtmulai)).' Agustus '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==9){
                                $tgl = date('d',strtotime($dtmulai)).' September '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==10){
                                $tgl = date('d',strtotime($dtmulai)).' Oktober '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==11){
                                $tgl = date('d',strtotime($dtmulai)).' November '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==12){
                                $tgl = date('d',strtotime($dtmulai)).' Desember '.date('Y',strtotime($dtmulai));
                            }
                            $sheet->setCellValue('A3', 'MINGGU KE');
                            $sheet->setCellValue('B3', ':');
                            $sheet->setCellValue('C3', ' '.$value->ke_jadwal);
                            $sheet->setCellValue('A4', 'HARI/TANGGAL');
                            $sheet->setCellValue('B4', ':');
                            $sheet->setCellValue('C4', $hari.' '.$tgl);
                            $sheet->setCellValue('A5', 'NAMA KEGIATAN');
                            $sheet->setCellValue('B5', ':');
                            $sheet->setCellValue('C5', $paket->nama_kegiatan);
                            $sheet->setCellValue('A6', 'NAMA PEKERJAAN');
                            $sheet->setCellValue('B6', ':');
                            $sheet->setCellValue('C6', $paket->nama_paket);
                            $sheet->setCellValue('A6', 'LOKASI PROYEK');
                            $sheet->setCellValue('B6', ':');
                            $sheet->setCellValue('C6', $paket->nama_desa);
                            $sheet->setCellValue('A7', 'TAHUN ANGGARAN');
                            $sheet->setCellValue('B7', ':');
                            $sheet->setCellValue('C7', ' '.$paket->tahun_anggaran);
                            $sheet->cell('A1', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->cell('A2', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->cell('G1', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('H4:K4');
                            $sheet->setCellValue('H4', 'KONTRAKTOR PELAKSANA');
                            $sheet->cell('H4', function($cell){
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('L4', ': '.strtoupper($paket->nama_kontraktor));
                            $sheet->cell('L4', function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('A8:D9');
                            $sheet->setCellValue('A8', 'TENAGA KERJA');
                            $sheet->cell('A8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->getStyle('A8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('E8:H9');
                            $sheet->setCellValue('E8', 'BAHAN');
                            $sheet->cell('E8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('E8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('I8:K9');
                            $sheet->setCellValue('I8', 'ALAT YANG DATANG DAN DIPAKAI');
                            $sheet->cell('I8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('I8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('L8:L9');
                            $sheet->setCellValue('L8', 'PEKERJAAN YANG DILAKSANAKAN PADA HARI INI');
                            $sheet->cell('L8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('L8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('A10:B11');
                            $sheet->setCellValue('A10', 'KEAHLIAN');
                            $sheet->cell('A10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('A10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('C10:D11');
                            $sheet->setCellValue('C10', 'JUMLAH');
                            $sheet->cell('C10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('C10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('E10:E11');
                            $sheet->setCellValue('E10', 'JENIS BAHAN YANG DIDATANGKAN');
                            $sheet->cell('E10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('E10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('F10:F11');
                            $sheet->setCellValue('F10', 'JUMLAH YANG DITERIMA');
                            $sheet->cell('F10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('F10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('G10:G11');
                            $sheet->setCellValue('G10', 'SATUAN');
                            $sheet->cell('G10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('G10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('H10:H11');
                            $sheet->setCellValue('H10', 'JUMLAH YANG DITOKLAK');
                            $sheet->cell('H10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('H10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('I10:I11');
                            $sheet->setCellValue('I10', 'MACAM ALAT');
                            $sheet->cell('I10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('I10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('J10:K11');
                            $sheet->setCellValue('J10', 'JUMLAH');
                            $sheet->cell('J10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('J10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('L10:L11');
                            $sheet->setCellValue('L10', 'MACAM PEKERJAAN');
                            $sheet->cell('L10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('L10')->getAlignment()->setWrapText(true);
                            $baris=12;
                            for ($i=0; $i < 16 ; $i++) { 
                                $sheet->mergeCells('A'.$baris.':B'.$baris);
                                $sheet->setCellValue('A'.$baris, '');
                                $sheet->cell('A'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('C'.$baris.':D'.$baris);
                                $sheet->setCellValue('C'.$baris,'');
                                $sheet->cell('C'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('E'.$baris,'                 ');
                                $sheet->cell('E'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('F'.$baris, '                 ');
                                $sheet->cell('F'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('G'.$baris, '                 ');
                                $sheet->cell('G'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('H'.$baris.':H'.$baris);
                                $sheet->setCellValue('H'.$baris, '                 ');
                                $sheet->cell('H'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('I'.$baris.':I'.$baris);
                                $sheet->setCellValue('I'.$baris, '                 ');
                                $sheet->cell('I'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('J'.$baris.':K'.$baris);
                                $sheet->setCellValue('J'.$baris, '       ');
                                $sheet->cell('J'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('L'.$baris, '               ');
                                $sheet->cell('L'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $baris++;
                            }
                            $sheet->mergeCells('A'.$baris.':H'.$baris);
                            $sheet->setCellValue('A'.$baris, 'PEKERJAAN DIMULAI JAM : 08.00 WIB           , SELESAI JAM : 16.00 WIB');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('I'.$baris.':I'.$baris);
                            $sheet->setCellValue('I'.$baris, '           ');
                            $sheet->cell('I'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->mergeCells('J'.$baris.':K'.$baris);
                            $sheet->setCellValue('J'.$baris, '');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->setCellValue('L'.$baris, '');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $baris++;
                            $sheet->mergeCells('A'.$baris.':H'.$baris);
                            $sheet->setCellValue('A'.$baris, 'HARI :   SEPENUHNYA DAPAT       DIPERGUNAKAN UNTUK BEKERJA');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('I'.$baris.':I'.$baris);
                            $sheet->setCellValue('I'.$baris, '           ');
                            $sheet->cell('I'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->mergeCells('J'.$baris.':K'.$baris);
                            $sheet->setCellValue('J'.$baris, '');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->setCellValue('L'.$baris, '                ');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $baris++;
                            $baris++;
                            $sheet->setCellValue('A'.$baris, 'Catatan :');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, 'Diperiksa / Disetujui Oleh:');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris, 'Dibuat / Diusulkan Oleh:');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, 'Konsultan Pengawas');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris, 'Penyedia Barang / Jasa');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, strtoupper($paket->nama_konsultan));
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->setCellValue('L'.$baris, strtoupper($paket->nama_kontraktor));
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, strtoupper($paket->direktur_konsultan));
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->setCellValue('L'.$baris, strtoupper($paket->direktur_kontraktor));
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, 'Pengawas');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->setCellValue('L'.$baris, 'Pelaksana');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                        });
                        $dtmulai = $dtmulai->addDays(1);
                    }
                } else {
                    $dtmulai = new Carbon($value->minggu_jadwal);
                    $dtselesai = new Carbon($value->minggu_jadwal);
                    $dtselesai = $dtselesai->addDays(6);
                    $jmlhari = $dtmulai->diffInDays($dtselesai, false);
                    for ($i=0; $i <=$jmlhari ; $i++) {
                        $hari = $dtmulai->format('l');
                        switch ($hari) {
                            case 'Sunday':
                                $hari = "MINGGU";
                                break;
                            case 'Monday':
                                $hari = "SENIN";
                                break;
                            case 'Tuesday':
                                $hari = "SELASA";
                                break;
                            case 'Wednesday':
                                $hari = "RABU";
                                break;
                            case 'Thursday':
                                $hari = "KAMIS";
                                break;
                            case 'Friday':
                                $hari = "JUMAT";
                                break;
                            case 'Saturday':
                                $hari = "SABTU";
                                break;
                            default:
                        }
                        $excel->sheet($hari.'('.$value->ke_jadwal.')', function($sheet) use ($paket, $value, $dtmulai, $hari) {
                            $sheet->mergeCells('A1:F1');
                            $sheet->mergeCells('A2:F2');
                            $sheet->mergeCells('G1:L2');
                            $sheet->setCellValue('A1', 'DINAS PEKERJAAN PEKERJAAN UMUM DAN TATA RUANG');
                            $sheet->setCellValue('A2', 'PEMERINTAH KABUPATEN GRESIK');
                            $sheet->setCellValue('G1', 'LAPORAN HARIAN');
                            $tgl ="";
                            if (date("m",strtotime($dtmulai))==1) {
                                $tgl = date('d',strtotime($dtmulai)).' Januari '.date('Y',strtotime($dtmulai));
                            } elseif (date("m",strtotime($dtmulai))==2){
                                $tgl = date('d',strtotime($dtmulai)).' Februari '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==3){
                                $tgl = date('d',strtotime($dtmulai)).' Maret '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==4){
                                $tgl = date('d',strtotime($dtmulai)).' April '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==5){
                                $tgl = date('d',strtotime($dtmulai)).' Mei '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==6){
                                $tgl = date('d',strtotime($dtmulai)).' Juni '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==7){
                                $tgl = date('d',strtotime($dtmulai)).' Juli '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==8){
                                $tgl = date('d',strtotime($dtmulai)).' Agustus '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==9){
                                $tgl = date('d',strtotime($dtmulai)).' September '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==10){
                                $tgl = date('d',strtotime($dtmulai)).' Oktober '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==11){
                                $tgl = date('d',strtotime($dtmulai)).' November '.date('Y',strtotime($dtmulai));
                            }elseif (date("m",strtotime($dtmulai))==12){
                                $tgl = date('d',strtotime($dtmulai)).' Desember '.date('Y',strtotime($dtmulai));
                            }
                            $sheet->setCellValue('A3', 'MINGGU KE');
                            $sheet->setCellValue('B3', ':');
                            $sheet->setCellValue('C3', ' '.$value->ke_jadwal);
                            $sheet->setCellValue('A4', 'HARI/TANGGAL');
                            $sheet->setCellValue('B4', ':');
                            $sheet->setCellValue('C4', $hari.' '.$tgl);
                            $sheet->setCellValue('A5', 'NAMA KEGIATAN');
                            $sheet->setCellValue('B5', ':');
                            $sheet->setCellValue('C5', $paket->nama_kegiatan);
                            $sheet->setCellValue('A6', 'NAMA PEKERJAAN');
                            $sheet->setCellValue('B6', ':');
                            $sheet->setCellValue('C6', $paket->nama_paket);
                            $sheet->setCellValue('A6', 'LOKASI PROYEK');
                            $sheet->setCellValue('B6', ':');
                            $sheet->setCellValue('C6', $paket->nama_desa);
                            $sheet->setCellValue('A7', 'TAHUN ANGGARAN');
                            $sheet->setCellValue('B7', ':');
                            $sheet->setCellValue('C7', ' '.$paket->tahun_anggaran);
                            $sheet->cell('A1', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->cell('A2', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->cell('G1', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('H4:K4');
                            $sheet->setCellValue('H4', 'KONTRAKTOR PELAKSANA');
                            $sheet->cell('H4', function($cell){
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('L4', ': '.strtoupper($paket->nama_kontraktor));
                            $sheet->cell('L4', function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('A8:D9');
                            $sheet->setCellValue('A8', 'TENAGA KERJA');
                            $sheet->cell('A8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->getStyle('A8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('E8:H9');
                            $sheet->setCellValue('E8', 'BAHAN');
                            $sheet->cell('E8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('E8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('I8:K9');
                            $sheet->setCellValue('I8', 'ALAT YANG DATANG DAN DIPAKAI');
                            $sheet->cell('I8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('I8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('L8:L9');
                            $sheet->setCellValue('L8', 'PEKERJAAN YANG DILAKSANAKAN PADA HARI INI');
                            $sheet->cell('L8', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('L8')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('A10:B11');
                            $sheet->setCellValue('A10', 'KEAHLIAN');
                            $sheet->cell('A10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('A10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('C10:D11');
                            $sheet->setCellValue('C10', 'JUMLAH');
                            $sheet->cell('C10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('C10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('E10:E11');
                            $sheet->setCellValue('E10', 'JENIS BAHAN YANG DIDATANGKAN');
                            $sheet->cell('E10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('E10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('F10:F11');
                            $sheet->setCellValue('F10', 'JUMLAH YANG DITERIMA');
                            $sheet->cell('F10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('F10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('G10:G11');
                            $sheet->setCellValue('G10', 'SATUAN');
                            $sheet->cell('G10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('G10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('H10:H11');
                            $sheet->setCellValue('H10', 'JUMLAH YANG DITOKLAK');
                            $sheet->cell('H10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('H10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('I10:I11');
                            $sheet->setCellValue('I10', 'MACAM ALAT');
                            $sheet->cell('I10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('I10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('J10:K11');
                            $sheet->setCellValue('J10', 'JUMLAH');
                            $sheet->cell('J10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('J10')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('L10:L11');
                            $sheet->setCellValue('L10', 'MACAM PEKERJAAN');
                            $sheet->cell('L10', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('L10')->getAlignment()->setWrapText(true);
                            $baris=12;
                            for ($i=0; $i < 16 ; $i++) { 
                                $sheet->mergeCells('A'.$baris.':B'.$baris);
                                $sheet->setCellValue('A'.$baris, '');
                                $sheet->cell('A'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('C'.$baris.':D'.$baris);
                                $sheet->setCellValue('C'.$baris,'');
                                $sheet->cell('C'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('E'.$baris,'                 ');
                                $sheet->cell('E'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('F'.$baris, '                 ');
                                $sheet->cell('F'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('G'.$baris, '                 ');
                                $sheet->cell('G'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('H'.$baris.':H'.$baris);
                                $sheet->setCellValue('H'.$baris, '                 ');
                                $sheet->cell('H'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('I'.$baris.':I'.$baris);
                                $sheet->setCellValue('I'.$baris, '                 ');
                                $sheet->cell('I'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->mergeCells('J'.$baris.':K'.$baris);
                                $sheet->setCellValue('J'.$baris, '       ');
                                $sheet->cell('J'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $sheet->setCellValue('L'.$baris, '               ');
                                $sheet->cell('L'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                });
                                $baris++;
                            }
                            $sheet->mergeCells('A'.$baris.':H'.$baris);
                            $sheet->setCellValue('A'.$baris, 'PEKERJAAN DIMULAI JAM : 08.00 WIB           , SELESAI JAM : 16.00 WIB');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('I'.$baris.':I'.$baris);
                            $sheet->setCellValue('I'.$baris, '           ');
                            $sheet->cell('I'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->mergeCells('J'.$baris.':K'.$baris);
                            $sheet->setCellValue('J'.$baris, '');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->setCellValue('L'.$baris, '');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $baris++;
                            $sheet->mergeCells('A'.$baris.':H'.$baris);
                            $sheet->setCellValue('A'.$baris, 'HARI :   SEPENUHNYA DAPAT       DIPERGUNAKAN UNTUK BEKERJA');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('I'.$baris.':I'.$baris);
                            $sheet->setCellValue('I'.$baris, '           ');
                            $sheet->cell('I'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->mergeCells('J'.$baris.':K'.$baris);
                            $sheet->setCellValue('J'.$baris, '');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->setCellValue('L'.$baris, '                ');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $baris++;
                            $baris++;
                            $sheet->setCellValue('A'.$baris, 'Catatan :');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, 'Diperiksa / Disetujui Oleh:');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris, 'Dibuat / Diusulkan Oleh:');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, 'Konsultan Pengawas');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris, 'Penyedia Barang / Jasa');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, strtoupper($paket->nama_konsultan));
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->setCellValue('L'.$baris, strtoupper($paket->nama_kontraktor));
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, '');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('L'.$baris,'');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, strtoupper($paket->direktur_konsultan));
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->setCellValue('L'.$baris, strtoupper($paket->direktur_kontraktor));
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setAlignment('left');
                            });
                            $sheet->mergeCells('B'.$baris.':F'.$baris);
                            $sheet->setCellValue('B'.$baris, '...........................................................................................................................................................................');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':K'.$baris);
                            $sheet->setCellValue('H'.$baris, 'Pengawas');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->setCellValue('L'.$baris, 'Pelaksana');
                            $sheet->cell('L'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                        });
                        $dtmulai = $dtmulai->addDays(1);
                    }
                }
            }
        })->export('xlsx');
    }

    public function downloadfile($id)
    {
        $idpro = DB::table('lapharian')->where('id_lapharian','=',$id)->first();
        $file= public_path(). "/images/dokumentasi/".$idpro->id_paket."/laporan/".$idpro->lokasi_lapharian;
        $headers = array(
              'Content-Type: application/pdf',
            );
        return Response::download($file, $idpro->lokasi_lapharian, $headers);
        
    }
    public function hapus($id)
    {
        $idpro = DB::table('lapharian')->where('id_lapharian','=',$id)->first();
        $path = 'images/dokumentasi/'.$idpro->id_paket.'/laporan/'.$idpro->lokasi_lapharian;
        if (File::exists($path)) {
                File::Delete($path);
        }
        DB::table('lapharian')->where('id_lapharian', '=',$id)->delete();
        Session::flash('message', 'Data Laporan Harian berhasil dihapus !');
        return Redirect::to('/lapharian/'.$idpro->id_paket);
    }
}
