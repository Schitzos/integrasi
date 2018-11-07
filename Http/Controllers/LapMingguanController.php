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

class LapMingguanController extends Controller
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
        return view('detail.lapmingguan')->with('data', $data)->with('jadwal',$jadwal);
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
    public function unduh($id,$minggu1,$minggu2)
    {

        $paket = DB::table('paket as p')
            ->join('dpa as a','p.id_dpa','=','a.id_dpa')
            ->join('kegiatan as k','a.id_kegiatan','=','k.id_kegiatan')
            ->join('program as r','k.id_program','=','r.id_program')
            ->leftjoin('kontraktor as t','p.id_kontraktor','=','t.id_kontraktor')
            ->leftjoin('konsultan as s','p.id_konsultan','=','s.id_konsultan')
            ->leftjoin('desa as d','p.id_desa','=','d.id_desa')
            ->join('pegawai as w','k.pptk','=','w.nip_pegawai')
            ->where('p.id_paket','=',$id)->first();
        $jadwal = DB::table('jadwal as j')
            ->join('paket as p','j.id_paket','=','p.id_paket')
            ->where('p.id_paket','=',$id)
            ->where('j.ke_jadwal','>=',$minggu1)
            ->where('j.ke_jadwal','<=',$minggu2)
            ->get();
        $harisebelum="";
        Excel::create('LAP MINGGUAN '. strtoupper($paket->nama_paket), function($excel) use ($paket, $jadwal,$harisebelum) {
            $excel->setTitle('LAP MINGGUAN '.$paket->nama_paket);
            $excel->setCreator('Dinas PU Gresik')->setCompany('Pemeritah Kabupaten Gresik');
            $excel->setDescription('Formulir Laporan Mingguan');
            foreach ($jadwal as $value) {
                $excel->sheet('M('.$value->ke_jadwal.')', function($sheet) use ($paket,$value,$harisebelum) {
                    $hari1="";
                    $hari2="";
                    $tglkon="";
                    $tglspmk="";
                    $tglselesai="";
                    if ($value->ke_jadwal==1) {
                        if (date("m",strtotime($paket->tgl_mulai))==1) {
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Januari ';
                        } elseif (date("m",strtotime($paket->tgl_mulai))==2){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Februari ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==3){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Maret ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==4){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' April ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==5){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Mei ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==6){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Juni ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==7){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Juli ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==8){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Agustus ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==9){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' September ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==10){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Oktober ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==11){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' November ';
                        }elseif (date("m",strtotime($paket->tgl_mulai))==12){
                            $hari1 = date('d',strtotime($paket->tgl_mulai)).' Desember ';
                        }
                        $tgl = new Carbon($value->minggu_jadwal); 
                        $tgl = $tgl->subDays(1);
                        if (date("m",strtotime($tgl))==1) {
                            $hari2 = date('d',strtotime($tgl)).' Januari '.date('Y',strtotime($tgl));
                        } elseif (date("m",strtotime($tgl))==2){
                            $hari2 = date('d',strtotime($tgl)).' Februari '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==3){
                            $hari2 = date('d',strtotime($tgl)).' Maret '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==4){
                            $hari2 = date('d',strtotime($tgl)).' April '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==5){
                            $hari2 = date('d',strtotime($tgl)).' Mei '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==6){
                            $hari2 = date('d',strtotime($tgl)).' Juni '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==7){
                            $hari2 = date('d',strtotime($tgl)).' Juli '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==8){
                            $hari2 = date('d',strtotime($tgl)).' Agustus '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==9){
                            $hari2 = date('d',strtotime($tgl)).' September '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==10){
                            $hari2 = date('d',strtotime($tgl)).' Oktober '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==11){
                            $hari2 = date('d',strtotime($tgl)).' November '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==12){
                            $hari2 = date('d',strtotime($tgl)).' Desember '.date('Y',strtotime($tgl));
                        }
                    } else {
                        if (date("m",strtotime($harisebelum))==1) {
                            $hari1 = date('d',strtotime($harisebelum)).' Januari ';
                        } elseif (date("m",strtotime($harisebelum))==2){
                            $hari1 = date('d',strtotime($harisebelum)).' Februari ';
                        }elseif (date("m",strtotime($harisebelum))==3){
                            $hari1 = date('d',strtotime($harisebelum)).' Maret ';
                        }elseif (date("m",strtotime($harisebelum))==4){
                            $hari1 = date('d',strtotime($harisebelum)).' April ';
                        }elseif (date("m",strtotime($harisebelum))==5){
                            $hari1 = date('d',strtotime($harisebelum)).' Mei ';
                        }elseif (date("m",strtotime($harisebelum))==6){
                            $hari1 = date('d',strtotime($harisebelum)).' Juni ';
                        }elseif (date("m",strtotime($harisebelum))==7){
                            $hari1 = date('d',strtotime($harisebelum)).' Juli ';
                        }elseif (date("m",strtotime($harisebelum))==8){
                            $hari1 = date('d',strtotime($harisebelum)).' Agustus ';
                        }elseif (date("m",strtotime($harisebelum))==9){
                            $hari1 = date('d',strtotime($harisebelum)).' September ';
                        }elseif (date("m",strtotime($harisebelum))==10){
                            $hari1 = date('d',strtotime($harisebelum)).' Oktober ';
                        }elseif (date("m",strtotime($harisebelum))==11){
                            $hari1 = date('d',strtotime($harisebelum)).' November ';
                        }elseif (date("m",strtotime($harisebelum))==12){
                            $hari1 = date('d',strtotime($harisebelum)).' Desember ';
                        }
                        $tgl = new Carbon($harisebelum); 
                        $tgl = $tgl->addDays(6);
                        if (date("m",strtotime($tgl))==1) {
                            $hari2 = date('d',strtotime($tgl)).' Januari '.date('Y',strtotime($tgl));
                        } elseif (date("m",strtotime($tgl))==2){
                            $hari2 = date('d',strtotime($tgl)).' Februari '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==3){
                            $hari2 = date('d',strtotime($tgl)).' Maret '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==4){
                            $hari2 = date('d',strtotime($tgl)).' April '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==5){
                            $hari2 = date('d',strtotime($tgl)).' Mei '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==6){
                            $hari2 = date('d',strtotime($tgl)).' Juni '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==7){
                            $hari2 = date('d',strtotime($tgl)).' Juli '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==8){
                            $hari2 = date('d',strtotime($tgl)).' Agustus '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==9){
                            $hari2 = date('d',strtotime($tgl)).' September '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==10){
                            $hari2 = date('d',strtotime($tgl)).' Oktober '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==11){
                            $hari2 = date('d',strtotime($tgl)).' November '.date('Y',strtotime($tgl));
                        }elseif (date("m",strtotime($tgl))==12){
                            $hari2 = date('d',strtotime($tgl)).' Desember '.date('Y',strtotime($tgl));
                        }
                    }
                    if (date("m",strtotime($paket->tgl_mulai))==1) {
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Januari '.date('Y',strtotime($paket->tgl_mulai));
                    } elseif (date("m",strtotime($paket->tgl_mulai))==2){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Februari '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==3){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Maret '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==4){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' April '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==5){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Mei '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==6){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Juni '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==7){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Juli '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==8){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Agustus '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==9){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' September '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==10){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Oktober '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==11){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' November '.date('Y',strtotime($paket->tgl_mulai));
                    }elseif (date("m",strtotime($paket->tgl_mulai))==12){
                        $tglkon = date('d',strtotime($paket->tgl_mulai)).' Desember '.date('Y',strtotime($paket->tgl_mulai));
                    }
                    if (date("m",strtotime($paket->tgl_spmk))==1) {
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Januari '.date('Y',strtotime($paket->tgl_spmk));
                    } elseif (date("m",strtotime($paket->tgl_spmk))==2){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Februari '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==3){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Maret '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==4){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' April '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==5){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Mei '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==6){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Juni '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==7){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Juli '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==8){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Agustus '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==9){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' September '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==10){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Oktober '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==11){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' November '.date('Y',strtotime($paket->tgl_spmk));
                    }elseif (date("m",strtotime($paket->tgl_spmk))==12){
                        $tglspmk = date('d',strtotime($paket->tgl_spmk)).' Desember '.date('Y',strtotime($paket->tgl_spmk));
                    }
                    if (date("m",strtotime($paket->tgl_selesai))==1) {
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Januari '.date('Y',strtotime($paket->tgl_selesai));
                    } elseif (date("m",strtotime($paket->tgl_selesai))==2){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Februari '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==3){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Maret '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==4){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' April '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==5){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Mei '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==6){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Juni '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==7){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Juli '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==8){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Agustus '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==9){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' September '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==10){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Oktober '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==11){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' November '.date('Y',strtotime($paket->tgl_selesai));
                    }elseif (date("m",strtotime($paket->tgl_selesai))==12){
                        $tglselesai = date('d',strtotime($paket->tgl_selesai)).' Desember '.date('Y',strtotime($paket->tgl_selesai));
                    }

                    $sheet->mergeCells('A1:R1');
                    $sheet->mergeCells('A3:C3');
                    $sheet->mergeCells('A4:C4');
                    $sheet->mergeCells('A5:C5');
                    $sheet->mergeCells('A6:C6');
                    $sheet->mergeCells('A7:C7');
                    // $sheet->mergeCells('F1:J2');
                    $sheet->setCellValue('A1', 'LAPORAN KEMAJUAN PEKERJAAN');
                    $sheet->setCellValue('A3', 'PROGRAM');
                    $sheet->setCellValue('D3', ':');
                    $sheet->setCellValue('E3', $paket->nama_program);
                    $sheet->setCellValue('A4', 'KEGIATAN');
                    $sheet->setCellValue('D4', ':');
                    $sheet->setCellValue('E4', $paket->nama_kegiatan);
                    $sheet->setCellValue('A5', 'PEKERJAAN');
                    $sheet->setCellValue('D5', ':');
                    $sheet->setCellValue('E5', $paket->nama_paket);
                    $sheet->setCellValue('A6', 'LOKASI');
                    $sheet->setCellValue('D6', ':');
                    $sheet->setCellValue('E6', $paket->nama_desa);
                    $sheet->setCellValue('A7', 'KONTRAKTOR');
                    $sheet->setCellValue('D7', ':');
                    $sheet->setCellValue('E7', $paket->nama_kontraktor);
                    $sheet->setCellValue('M2', 'MINGGU KE-'.$value->ke_jadwal);
                    $sheet->setCellValue('M3', 'TANGGAL');
                    $sheet->setCellValue('O3', ':');
                    $sheet->setCellValue('P3', $hari1.' - '.$hari2);
                    $sheet->setCellValue('M4', 'NO. KONTRAK');
                    $sheet->setCellValue('O4', ':');
                    $sheet->setCellValue('P4', $paket->nomor_kontrak);
                    $sheet->setCellValue('M5', 'TGL. KONTRAK');
                    $sheet->setCellValue('O5', ':');
                    $sheet->setCellValue('P5', $tglkon);
                    $sheet->setCellValue('M6', 'PEKERJAAN DIMULAI & AKHIR');
                    $sheet->setCellValue('O6', ':');
                    $sheet->setCellValue('P6', $tglspmk.' s/d '.$tglselesai);
                    $sheet->setCellValue('M7', 'NILAI KONTRAK');
                    $sheet->setCellValue('O7', ':');
                    $sheet->setCellValue('P7', 'Rp. '.number_format($paket->nilai_kontrak,2,",","."));
                    $sheet->cell('A1', function($cell){
                        $cell->setFontWeight('bold');
                        $cell->setFontSize(16);
                    });
                    $sheet->cell('A3', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->cell('A4', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->cell('A5', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->cell('A6', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->cell('A7', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->mergeCells('A9:A12');
                    $sheet->mergeCells('B9:E12');
                    $sheet->mergeCells('F9:F12');
                    $sheet->mergeCells('G9:G12');
                    $sheet->mergeCells('H9:H12');
                    $sheet->mergeCells('I9:I12');
                    $sheet->mergeCells('J9:J12');
                    $sheet->mergeCells('K9:K12');
                    $sheet->mergeCells('L9:L12');
                    $sheet->mergeCells('M9:M12');
                    $sheet->mergeCells('N9:N12');
                    $sheet->mergeCells('O9:P12');
                    $sheet->mergeCells('Q9:Q12');
                    $sheet->mergeCells('R9:R12');
                    $sheet->setCellValue('A9', 'NO');
                    $sheet->setCellValue('B9', 'URAIAN PEKERJAAN');
                    $sheet->setCellValue('F9', 'SAT');
                    $sheet->setCellValue('G9', 'VOLUME');
                    $sheet->setCellValue('H9', 'HARGA PEKERJAAN');
                    $sheet->setCellValue('I9', 'BOBOT');
                    $sheet->setCellValue('J9', 'VOLUME MINGGU LALU');
                    $sheet->setCellValue('K9', 'VOLUME MINGGU INI');
                    $sheet->setCellValue('L9', 'VOLUME MINGGU TOTAL (8+7)');
                    $sheet->setCellValue('M9', 'KEMAJUAN FISIK MINGGU LALU  (7/4)');
                    $sheet->setCellValue('N9', 'KEMAJUAN FISIK MINGGU INI (8/4) ');
                    $sheet->setCellValue('O9', 'TAHAP PENYELESAIAN PEKERJAAN (10+11)');
                    $sheet->setCellValue('Q9', 'TINGKAT PENYELESAIAN SELURUH PEKERJAAN');
                    $sheet->setCellValue('R9', 'KET. / CATATAN');
                    $sheet->setCellValue('A13', '1');
                    $sheet->cell('A9', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $sheet->getStyle('A9')->getAlignment()->setWrapText(true);
                    $sheet->cell('B9', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $sheet->getStyle('B9')->getAlignment()->setWrapText(true);
                    $cellnya = 'F';
                    for ($i=0; $i < 9; $i++) { 
                        $sheet->cell($cellnya.'9', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setFontWeight('bold');
                        });
                        $sheet->getStyle($cellnya.'9')->getAlignment()->setWrapText(true);
                        $cellnya++;
                    }
                    $sheet->cell('O9', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setFontWeight('bold');
                    });
                    $sheet->getStyle('O9')->getAlignment()->setWrapText(true);
                    $sheet->cell('Q9', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setFontWeight('bold');
                    });
                    $sheet->getStyle('Q9')->getAlignment()->setWrapText(true);
                    $sheet->cell('R9', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                            $cell->setFontWeight('bold');
                    });
                    $sheet->getStyle('R9')->getAlignment()->setWrapText(true);

                    $sheet->setCellValue('A13', '');
                    $sheet->cell('A13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $sheet->mergeCells('B13:E13');
                    $sheet->setCellValue('B13', '');
                    $sheet->cell('B13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $cellnya = 'F';
                    for ($i = 0; $i < 2; $i++) {
                        $sheet->setCellValue($cellnya.'13', '');
                        $sheet->cell($cellnya.'13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $cellnya++;
                    }
                    $sheet->setCellValue('H13', '(Rp)');
                    $sheet->cell('H13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $sheet->setCellValue('I13', '(%)');
                    $sheet->cell('I13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $cellnya = 'J';
                    for ($i = 0; $i < 3; $i++) {
                        $sheet->setCellValue($cellnya.'13', '');
                        $sheet->cell($cellnya.'13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $cellnya++;
                    }
                    $sheet->setCellValue('M13', '(%)');
                    $sheet->cell('M13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $sheet->setCellValue('N13', '(%)');
                    $sheet->cell('N13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $sheet->mergeCells('O13:P13');
                    $sheet->setCellValue('O13', '(%)');
                    $sheet->cell('O13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                    });
                    $sheet->setCellValue('Q13', '(%)');
                    $sheet->cell('Q13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                    });
                    $sheet->setCellValue('R13', '');
                    $sheet->cell('R13', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                    });

                    $sheet->setCellValue('A14', '1');
                    $sheet->cell('A14', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $sheet->mergeCells('B14:E14');
                    $sheet->setCellValue('B14', '2');
                    $sheet->cell('B14', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                    $cellnya = 'F';
                    for ($i = 0; $i < 9; $i++) {
                        $sheet->setCellValue($cellnya.'14', $i+3);
                        $sheet->cell($cellnya.'14', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $cellnya++;
                    }
                    $sheet->mergeCells('O14:P14');
                    $sheet->setCellValue('O14', '12');
                    $sheet->cell('O14', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                    });
                    $sheet->setCellValue('Q14', '13');
                    $sheet->cell('Q14', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                    });
                    $sheet->setCellValue('R14', '14');
                    $sheet->cell('R14', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                    });
                    //isi detail
                    if ($value->ke_jadwal==1) {
                        $realisasi = DB::table('detail_rab as d')
                            ->join('rab_paket as r','d.id_rab_paket','=','r.id_rab_paket')
                            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                            ->where('d.minggu_ke','=',$value->ke_jadwal)
                            ->where('j.id_paket','=',$paket->id_paket)
                            ->get();
                        $lalu = null;
                    } else {
                        $realisasi = DB::table('detail_rab as d')
                            ->join('rab_paket as r','d.id_rab_paket','=','r.id_rab_paket')
                            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                            ->where('d.minggu_ke','=',$value->ke_jadwal)
                            ->where('j.id_paket','=',$paket->id_paket)
                            ->get();
                        $lalu= DB::table('detail_rab as d')
                            ->join('rab_paket as r','d.id_rab_paket','=','r.id_rab_paket')
                            ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                            ->where('d.minggu_ke','=',$value->ke_jadwal-1)
                            ->where('j.id_paket','=',$paket->id_paket)
                            ->get();
                    }
                    $rab = DB::table('rab_paket as r')
                        ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                        ->where('j.id_paket','=',$paket->id_paket)
                        ->get();
                    $jenis = DB::table('jenis_rab')->where('id_paket','=',$paket->id_paket)->get();
                    $baris =15;
                    $nom ='A';
                    $nomor=1;
                    $total=0;
                    $grandtot =0;
                    $granjum =0;
                    $grandsum=0;
                    foreach ($jenis as $jen) {
                        $sheet->setCellValue('A'.$baris, $nom);
                        $sheet->cell('A'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setFontWeight('bold');
                        });
                        $sheet->mergeCells('B'.$baris.':E'.$baris);
                        $sheet->setCellValue('B'.$baris, $jen->nama_jenis_rab);
                        $sheet->cell('B'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('left');
                            $cell->setFontWeight('bold');
                        });
                        $awal='F';
                        for ($i=0; $i < 9; $i++) { 
                            $sheet->setCellValue($awal.$baris, '');
                            $sheet->cell($awal.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                                $cell->setFontWeight('bold');
                            });
                            $awal++;
                        }
                        $sheet->mergeCells('O'.$baris.':P'.$baris);
                        $sheet->setCellValue('O'.$baris, '');
                        $sheet->cell('O'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('left');
                            $cell->setFontWeight('bold');
                        });
                        $sheet->setCellValue('Q'.$baris, '');
                        $sheet->cell('Q'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('left');
                            $cell->setFontWeight('bold');
                        });
                        $sheet->setCellValue('R'.$baris, '');
                        $sheet->cell('R'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('left');
                            $cell->setFontWeight('bold');
                        });
                        $baris++;
                        $total=0;
                        $jum =0;
                        $sum=0;
                        $kolom4 =0;$kolom5 =0;$kolom6 =0;$kolom7 =0;$kolom8 =0;$kolom9 =0;$kolom10 =0;$kolom11 =0;$kolom12=0;$kolom13=0;
                        foreach ($rab as $r) {
                            if ($jen->id_jenis_rab==$r->id_jenis_rab) {
                                $sheet->setCellValue('A'.$baris, $nomor);
                                $sheet->cell('A'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $sheet->mergeCells('B'.$baris.':E'.$baris);
                                $sheet->setCellValue('B'.$baris, $r->pekerjaan_rab_paket);
                                $sheet->cell('B'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('left');
                                });
                                $sheet->setCellValue('F'.$baris, $r->satuan_rab_paket);
                                $sheet->cell('F'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('center');
                                });
                                $kolom4 = $r->volume_rab_paket;
                                $sheet->setCellValue('G'.$baris, $r->volume_rab_paket);
                                $sheet->cell('G'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('center');
                                });
                                $kolom5 = $r->harga_rab_paket;
                                $sheet->setCellValue('H'.$baris, number_format($r->harga_rab_paket,2,",","."));
                                $sheet->cell('H'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $subtotal = DB::table('rab_paket as r')
                                    ->join('jenis_rab as j','r.id_jenis_rab','=','j.id_jenis_rab')
                                    ->select(DB::raw('SUM(r.harga_rab_paket) AS total'))
                                    ->where('j.id_paket','=',$paket->id_paket)
                                    ->first();
                                $kolom6 = ($kolom5/$subtotal->total)*100;
                                $sheet->setCellValue('I'.$baris,$kolom6);
                                $sheet->cell('I'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $jum+=$kolom6;
                                foreach ($realisasi as $l) {
                                    if ($r->id_rab_paket==$l->id_rab_paket) {
                                        if ($value->ke_jadwal==1) {
                                            $kolom7 =0.00000;
                                            $sheet->setCellValue('J'.$baris,$kolom7);
                                            $sheet->cell('J'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $kolom8= $l->isi_detail_rab;
                                            $sheet->setCellValue('K'.$baris,$l->isi_detail_rab);
                                            $sheet->cell('K'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $kolom9 = $kolom7+$kolom8;
                                            $sheet->setCellValue('L'.$baris,$kolom9);
                                            $sheet->cell('L'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $kolom10 = ($kolom7/$kolom4)*100;
                                            $sheet->setCellValue('M'.$baris,$kolom10);
                                            $sheet->cell('M'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $kolom11 = ($kolom8/$kolom4)*100;
                                            $sheet->setCellValue('N'.$baris,$kolom11);
                                            $sheet->cell('N'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $kolom12 = $kolom10+$kolom11;
                                            $sheet->mergeCells('O'.$baris.':P'.$baris);
                                            $sheet->setCellValue('O'.$baris,$kolom12);
                                            $sheet->cell('O'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $kolom13= (($kolom6/100)*($kolom12/100))*100;
                                            $sheet->setCellValue('Q'.$baris,$kolom13);
                                            $sheet->cell('Q'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $sheet->setCellValue('R'.$baris,'');
                                            $sheet->cell('R'.$baris, function($cell){
                                                $cell->setBorder('thin','thin','thin','thin');
                                                $cell->setAlignment('right');
                                            });
                                            $sum+=$kolom13;
                                        } else {
                                            foreach ($lalu as $u) {
                                                if ($l->id_rab_paket==$u->id_rab_paket) {
                                                    $sheet->setCellValue('I'.$baris,$kolom6);
                                                    $sheet->cell('I'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $kolom7 =$u->isi_detail_rab;
                                                    $sheet->setCellValue('J'.$baris,$kolom7);
                                                    $sheet->cell('J'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $kolom8= $l->isi_detail_rab;
                                                    $sheet->setCellValue('K'.$baris,$l->isi_detail_rab);
                                                    $sheet->cell('K'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $kolom9 = $kolom7+$kolom8;
                                                    $sheet->setCellValue('L'.$baris,$kolom9);
                                                    $sheet->cell('L'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $kolom10 = ($kolom7/$kolom4)*100;
                                                    $sheet->setCellValue('M'.$baris,$kolom10);
                                                    $sheet->cell('M'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $kolom11 = ($kolom8/$kolom4)*100;
                                                    $sheet->setCellValue('N'.$baris,$kolom11);
                                                    $sheet->cell('N'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $kolom12 = $kolom10+$kolom11;
                                                    $sheet->mergeCells('O'.$baris.':P'.$baris);
                                                    $sheet->setCellValue('O'.$baris,$kolom12);
                                                    $sheet->cell('O'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $kolom13=(($kolom6/100)*($kolom12/100))*100;
                                                    $sheet->setCellValue('Q'.$baris,$kolom13);
                                                    $sheet->cell('Q'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $sheet->setCellValue('R'.$baris,'');
                                                    $sheet->cell('R'.$baris, function($cell){
                                                        $cell->setBorder('thin','thin','thin','thin');
                                                        $cell->setAlignment('right');
                                                    });
                                                    $sum +=$kolom13;
                                                }    
                                            }
                                        }
                                    }
                                }
                                $total+= $r->harga_rab_paket;
                                $baris++;
                                $nomor++;
                            }
                        }
                        $nomor=1;
                        $sheet->setCellValue('A'.$baris, '');
                        $sheet->cell('A'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                        });
                        $sheet->mergeCells('B'.$baris.':E'.$baris);
                        $sheet->setCellValue('B'.$baris, 'Sub total');
                        $sheet->cell('B'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('right');
                            $cell->setFontWeight('bold');
                        });
                        $sheet->setCellValue('F'.$baris,'');
                        $sheet->cell('F'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setFontWeight('bold');
                        });
                        $sheet->setCellValue('G'.$baris, '');
                        $sheet->cell('G'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('center');
                            $cell->setFontWeight('bold');
                        });
                        $grandtot += $total;
                        $sheet->setCellValue('H'.$baris, number_format($total,2,",","."));
                        $sheet->cell('H'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('right');
                            $cell->setFontWeight('bold');
                        });
                        $granjum+=$jum;
                        $sheet->setCellValue('I'.$baris, $jum);
                        $sheet->cell('I'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('right');
                            $cell->setFontWeight('bold');
                        });
                        $awal='J';
                        for ($i=0; $i < 5; $i++) { 
                            $sheet->setCellValue($awal.$baris, '');
                            $sheet->cell($awal.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                                $cell->setFontWeight('bold');
                            });
                            $awal++;
                        }
                        $sheet->mergeCells('O'.$baris.':P'.$baris);
                        $sheet->setCellValue('O'.$baris, '');
                        $sheet->cell('O'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('right');
                            $cell->setFontWeight('bold');
                        });
                        $grandsum+=$sum;
                        $sheet->setCellValue('Q'.$baris, $sum);
                        $sheet->cell('Q'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('right');
                            $cell->setFontWeight('bold');
                        });
                        $sheet->setCellValue('R'.$baris, '');
                        $sheet->cell('R'.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('right');
                            $cell->setFontWeight('bold');
                        });
                        $baris++;
                        $nom++;
                    }
                    $sheet->setCellValue('A'.$baris, '');
                    $sheet->cell('A'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('B'.$baris.':E'.$baris);
                    $sheet->setCellValue('B'.$baris, 'Total keseluruhan');
                    $sheet->cell('B'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('right');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('F'.$baris,'');
                    $sheet->cell('F'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('G'.$baris, '');
                    $sheet->cell('G'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('center');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('H'.$baris, number_format($grandtot,2,",","."));
                    $sheet->cell('H'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('right');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('I'.$baris, $granjum);
                    $sheet->cell('I'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('right');
                        $cell->setFontWeight('bold');
                    });
                    $awal='J';
                    for ($i=0; $i < 5; $i++) { 
                        $sheet->setCellValue($awal.$baris, '');
                        $sheet->cell($awal.$baris, function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setAlignment('left');
                            $cell->setFontWeight('bold');
                        });
                        $awal++;
                    }
                    $sheet->mergeCells('O'.$baris.':P'.$baris);
                    $sheet->setCellValue('O'.$baris, '');
                    $sheet->cell('O'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('right');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('Q'.$baris, $grandsum);
                    $sheet->cell('Q'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('right');
                        $cell->setFontWeight('bold');
                    });
                    $sheet->setCellValue('R'.$baris, '');
                    $sheet->cell('R'.$baris, function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                        $cell->setAlignment('right');
                        $cell->setFontWeight('bold');
                    });
                    $baris++;
                    $tglcetak="";
                    if (date("m")==1) {
                        $tglcetak = date('d').' Januari '.date('Y');
                    } elseif (date("m")==2){
                        $tglcetak = date('d').' Februari '.date('Y');
                    }elseif (date("m")==3){
                        $tglcetak = date('d').' Maret '.date('Y');
                    }elseif (date("m")==4){
                        $tglcetak = date('d').' April '.date('Y');
                    }elseif (date("m")==5){
                        $tglcetak = date('d').' Mei '.date('Y');
                    }elseif (date("m")==6){
                        $tglcetak = date('d').' Juni '.date('Y');
                    }elseif (date("m")==7){
                        $tglcetak = date('d').' Juli '.date('Y');
                    }elseif (date("m")==8){
                        $tglcetak = date('d').' Agustus '.date('Y');
                    }elseif (date("m")==9){
                        $tglcetak = date('d').' September '.date('Y');
                    }elseif (date("m")==10){
                        $tglcetak = date('d').' Oktober '.date('Y');
                    }elseif (date("m")==11){
                        $tglcetak = date('d').' November '.date('Y');
                    }elseif (date("m")==12){
                        $tglcetak = date('d').' Desember '.date('Y');
                    }
                    $sheet->mergeCells('P'.$baris.':Q'.$baris);
                    $sheet->setCellValue('P'.$baris, $tglcetak);
                    $sheet->cell('P'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $baris++;
                    $sheet->mergeCells('B'.$baris.':E'.$baris);
                    $sheet->setCellValue('B'.$baris, 'Mengetahui / menyetujui');
                    $sheet->cell('B'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('J'.$baris.':L'.$baris);
                    $sheet->setCellValue('J'.$baris, 'Diperiksa / disetujui');
                    $sheet->cell('J'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('O'.$baris.':Q'.$baris);
                    $sheet->setCellValue('O'.$baris, 'Dibuat oleh :');
                    $sheet->cell('O'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $baris++;
                    $sheet->mergeCells('B'.$baris.':E'.$baris);
                    $sheet->setCellValue('B'.$baris, 'PEJABAT PELAKSANA TEKNIS KEGIATAN');
                    $sheet->cell('B'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('J'.$baris.':L'.$baris);
                    $sheet->setCellValue('J'.$baris, 'KONSULTAN PENGAWAS');
                    $sheet->cell('J'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('O'.$baris.':Q'.$baris);
                    $sheet->setCellValue('O'.$baris, 'KONTRAKTOR PELAKSANA');
                    $sheet->cell('O'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $baris++;
                    $sheet->mergeCells('B'.$baris.':E'.$baris);
                    $sheet->setCellValue('B'.$baris, 'PPTK');
                    $sheet->cell('B'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('J'.$baris.':L'.$baris);
                    $sheet->setCellValue('J'.$baris, $paket->nama_konsultan);
                    $sheet->cell('J'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('O'.$baris.':Q'.$baris);
                    $sheet->setCellValue('O'.$baris, $paket->nama_kontraktor);
                    $sheet->cell('O'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $baris++;
                    $baris++;
                    $baris++;
                    $baris++;
                    $sheet->mergeCells('B'.$baris.':E'.$baris);
                    $sheet->setCellValue('B'.$baris, $paket->nama_pegawai);
                    $sheet->cell('B'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('J'.$baris.':L'.$baris);
                    $sheet->setCellValue('J'.$baris, $paket->direktur_konsultan);
                    $sheet->cell('J'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('O'.$baris.':Q'.$baris);
                    $sheet->setCellValue('O'.$baris, $paket->direktur_kontraktor);
                    $sheet->cell('O'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $baris++;
                    $sheet->mergeCells('B'.$baris.':E'.$baris);
                    $sheet->setCellValue('B'.$baris, $paket->nip_pegawai);
                    $sheet->cell('B'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('J'.$baris.':L'.$baris);
                    $sheet->setCellValue('J'.$baris, 'Pengawas');
                    $sheet->cell('J'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                    $sheet->mergeCells('O'.$baris.':Q'.$baris);
                    $sheet->setCellValue('O'.$baris, 'Pelaksana');
                    $sheet->cell('O'.$baris, function($cell){
                        $cell->setAlignment('center');
                    });
                });
                $harisebelum = $value->minggu_jadwal;
            }
        })->export('xlsx');
    }
}
