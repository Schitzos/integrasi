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

class LapBulananController extends Controller
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
        $jadwal = DB::table('jadwal')
            ->select(DB::raw('DISTINCT(MONTH(minggu_jadwal)) AS bulan'))
            ->where('id_paket','=',$id)->get();
        return view('detail.lapbulanan')->with('data', $data)->with('jadwal',$jadwal);
    }
    public function unduh($id,$bulan)
    {
        $paket = DB::table('paket as p')
            ->join('dpa as a','p.id_dpa','=','a.id_dpa')
            ->join('kegiatan as k','a.id_kegiatan','=','k.id_kegiatan')
            ->join('program as r','k.id_program','=','r.id_program')
            ->leftjoin('kontraktor as t','p.id_kontraktor','=','t.id_kontraktor')
            ->leftjoin('konsultan as s','p.id_konsultan','=','s.id_konsultan')
            ->leftjoin('desa as d','p.id_desa','=','d.id_desa')
            ->join('pegawai as w','k.ppk','=','w.nip_pegawai')
            ->join('bidang as b','w.id_bidang','=','b.id_bidang')
            ->join('golongan as g','w.id_golongan','=','g.id_golongan')
            ->where('p.id_paket','=',$id)->first();
        Excel::create('LAP BULANAN '. strtoupper($paket->nama_paket), function($excel) use ($paket, $bulan) {
            $excel->setTitle('LAP BULANAN '.$paket->nama_paket);
            $excel->setCreator('Dinas PU Gresik')->setCompany('Pemeritah Kabupaten Gresik');
            $excel->setDescription('Formulir Laporan Mingguan');
            $jenis = DB::table('jenis_rab')->where('id_paket','=',$paket->id_paket)->get();
            if ($bulan==0) {
                $jmlbulan = DB::table('jadwal')
                    ->select(DB::raw('COUNT(DISTINCTMONTH(minggu_jadwal)) AS jml_bulan'))
                    ->where('id_paket','=',$paket->id_paket)
                    ->first();
                $bul =DB::table('jadwal')
                    ->select(DB::raw('DISTINCT MONTH(minggu_jadwal) AS bulan'))
                    ->where('id_paket','=',$paket->id_paket)
                    ->first();
                $bulannya = $bul->bulan;
            } else {
                $jmlbulan = DB::table('jadwal')
                    ->select(DB::raw('COUNT(DISTINCT(MONTH(minggu_jadwal))) AS jml_bulan'))
                    ->where('id_paket','=',$paket->id_paket)
                    ->where(DB::raw('MONTH(minggu_jadwal)'),'=',$bulan)
                    ->first();
                $bulannya=$bulan;
            }
            $total = DB::table('rab_paket AS r')
                    ->join('jenis_rab AS j','r.id_jenis_rab','=','j.id_jenis_rab')
                    ->select(DB::raw('SUM(r.volume_rab_paket * r.harga_rab_paket) AS total'))
                    ->where('j.id_paket','=',$paket->id_paket)->first();
            for ($i=0; $i < $jmlbulan->jml_bulan; $i++) {
                $inya = $i+1;
                $excel->sheet('MC('.$inya.')', function($sheet) use ($paket,$inya,$bulannya,$total) {
                    $tglkon ='';
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
                    $sheet->setCellValue('B1', 'PEMERINTAH KABUPATEN GRESIK'.$bulannya);
                    $sheet->setCellValue('B2', 'DINAS PEKERJAAN UMUM DAN TATA RUANG');
                    $sheet->setCellValue('B3', strtoupper($paket->nama_bidang));
                    $sheet->setCellValue('B4', 'TAHUN ANGGARAN '.$paket->periode);
                    $sheet->cell('B1', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->cell('B2', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->cell('B3', function($cell){
                        $cell->setFontWeight('bold');
                        $cell->setFontSize(16);
                    });
                    $sheet->cell('B4', function($cell){
                        $cell->setFontWeight('bold');
                    });
                    $sheet->cell('A1:J5', function($cell){
                        $cell->setBorder('thin','thin','thin','thin');
                    });
                    $sheet->setCellValue('B7', 'PROGRAM');
                    $sheet->setCellValue('C7', ':');
                    $sheet->setCellValue('D7', strtoupper($paket->nama_program));
                    $sheet->setCellValue('B8', 'KEGIATAN');
                    $sheet->setCellValue('C8', ':');
                    $sheet->setCellValue('D8', strtoupper($paket->nama_kegiatan));
                    $sheet->setCellValue('B9', 'PEKERJAAN');
                    $sheet->setCellValue('C9', ':');
                    $sheet->setCellValue('D9', strtoupper($paket->nama_paket));
                    $sheet->setCellValue('B10', 'LOKASI');
                    $sheet->setCellValue('C10', ':');
                    $sheet->setCellValue('D10', strtoupper($paket->nama_desa));
                    $sheet->setCellValue('B11', 'NOMOR KONTRAK');
                    $sheet->setCellValue('C11', ':');
                    $sheet->setCellValue('D11', $paket->nomor_kontrak);
                    $sheet->setCellValue('B12', 'TANGGAL KONTRAK');
                    $sheet->setCellValue('C12', ':');
                    $sheet->setCellValue('D12', $tglkon);
                    $adaaden = DB::table('adendum')->select(DB::raw('COUNT(*) AS jml'))
                        ->where('id_paket','=',$paket->id_paket)
                        ->where(DB::raw('MONTH(tgl_kontrak_adendum)'),'=',$bulannya)
                        ->first();
                    if ($adaaden->jml>=1) {
                            $adendum = DB::table('adendum')
                                ->where('id_paket','=',$paket->id_paket)
                                ->where(DB::raw('MONTH(tgl_kontrak_adendum)'),'=',$bulannya)
                                ->first();
                            $tgladen ='';
                            if (date("m",strtotime($adendum->tgl_kontrak_adendum))==1) {
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Januari '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            } elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==2){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Februari '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==3){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Maret '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==4){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' April '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==5){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Mei '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==6){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Juni '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==7){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Juli '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==8){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Agustus '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==9){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' September '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==10){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Oktober '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==11){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' November '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }elseif (date("m",strtotime($adendum->tgl_kontrak_adendum))==12){
                                $tgladen = date('d',strtotime($adendum->tgl_kontrak_adendum)).' Desember '.date('Y',strtotime($adendum->tgl_kontrak_adendum));
                            }
                            $sheet->setCellValue('B13', 'NOMOR KONTRAK ADENDUM');
                            $sheet->setCellValue('C13', ':');
                            $sheet->setCellValue('D13', $adendum->no_kontrak_adendum);
                            $sheet->setCellValue('B14', 'TANGGAL KONTRAK AENDUM');
                            $sheet->setCellValue('C14', ':');
                            $sheet->setCellValue('D14', $tgladen);
                            $sheet->setCellValue('B15', 'KONTRAKTOR');
                            $sheet->setCellValue('C15', ':');
                            $sheet->setCellValue('D15', strtoupper($paket->nama_kontraktor));
                            $sheet->setCellValue('B16', 'NILAI KONTRAK');
                            $sheet->setCellValue('C16', ':');
                            $sheet->setCellValue('D16', 'Rp. '.number_format($paket->nilai_kontrak,2,",","."));
                            $sheet->cell('D15', function($cell){
                                $cell->setFontWeight('bold');
                            });
                            $sheet->cell('D16', function($cell){
                                $cell->setFontWeight('bold');
                            });
                            $sheet->setCellValue('F17', 'KONTRAKTOR');
                            $sheet->setCellValue('H17', ':');
                            $sheet->setCellValue('I17', 'APBD '.$paket->periode);
                            $sheet->setCellValue('F18', 'KODE REKENING');
                            $sheet->setCellValue('H18', ':');
                            $sheet->setCellValue('I18', $paket->id_kegiatan);
                            $sheet->setCellValue('F19', 'LAPORAN BULAN KE');
                            $sheet->setCellValue('H19', ':');
                            $sheet->setCellValue('I19', ' '.$inya.' ');
                            $sheet->setCellValue('F20', 'TANGGAL');
                            $sheet->setCellValue('H20', ':');
                            $tgljadwal = DB::table('jadwal')
                                ->select(DB::raw('MIN(minggu_jadwal) as mulai'),DB::raw('MAX(minggu_jadwal) as selesai'))
                                ->where('id_paket','=',$paket->id_paket)
                                ->where(DB::raw('MONTH(minggu_jadwal)'),'=',$bulannya)
                                ->first();
                            $tgl1= date("d",strtotime($tgljadwal->mulai));
                            $tgl2='';
                            if (date("m",strtotime($tgljadwal->selesai))==1) {
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Januari '.date('Y',strtotime($tgljadwal->selesai));
                            } elseif (date("m",strtotime($tgljadwal->selesai))==2){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Februari '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==3){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Maret '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==4){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' April '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==5){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Mei '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==6){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Juni '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==7){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Juli '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==8){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Agustus '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==9){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' September '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==10){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Oktober '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==11){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' November '.date('Y',strtotime($tgljadwal->selesai));
                            }elseif (date("m",strtotime($tgljadwal->selesai))==12){
                                $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Desember '.date('Y',strtotime($tgljadwal->selesai));
                            }
                            $sheet->setCellValue('I20', $tgl1.' s/d '.$tgl2 );
                            $jenis = DB::table('jenis_rab')->where('id_paket','=',$paket->id_paket)->get();
                            $sheet->mergeCells('A22:A23');
                            $sheet->setCellValue('A22', 'NO');
                            $sheet->cell('A22', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('A22')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('B22:E23');
                            $sheet->setCellValue('B22', 'URAIAN');
                            $sheet->cell('B22', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('B22')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('F22:G22');
                            $sheet->setCellValue('F22', 'KONTRAK');
                            $sheet->cell('F22', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('F22')->getAlignment()->setWrapText(true);
                            $sheet->setCellValue('F23', 'NILAI (Rp.)');
                            $sheet->cell('F23', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('F23')->getAlignment()->setWrapText(true);
                            $sheet->setCellValue('G23', 'BOBOT (%)');
                            $sheet->cell('G23', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('G23')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('H22:J22');
                            $sheet->setCellValue('H22', 'PEKERJAAN YANG DISELESAIKAN');
                            $sheet->cell('H22', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('H22')->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('H23:I23');
                            $sheet->setCellValue('H23', 'NILAI (Rp.)');
                            $sheet->cell('H23', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('H23')->getAlignment()->setWrapText(true);
                            $sheet->setCellValue('J23', 'BOBOT (%)');
                            $sheet->cell('J23', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setValignment('center');
                                $cell->setAlignment('center');
                            });
                            $sheet->getStyle('J23')->getAlignment()->setWrapText(true);
                            $sheet->cell('A6:J21', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $baris = 24;
                            $awal = 'A';
                            $tot1=0;
                            $tot2=0;
                            $tot3=0;
                            $tot4=0;
                            $detail = DB::table('detail_rab AS d')
                                ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
                                ->join('jenis_rab AS jr','r.id_jenis_rab','=','jr.id_jenis_rab')
                                ->join('paket AS p','jr.id_paket','=','p.id_paket')
                                ->join('jadwal AS j','d.minggu_ke','=','j.ke_jadwal')
                                ->select('jr.nama_jenis_rab AS pekerjaan',DB::raw('MONTH(j.minggu_jadwal) AS bulan'),DB::raw('r.volume_rab_paket * r.harga_rab_paket AS kontrak'),DB::raw('SUM(d.isi_detail_rab * r.harga_rab_paket) AS total_realisasi'))
                                ->where('p.id_paket','=',$paket->id_paket)
                                ->where('j.id_paket','=',$paket->id_paket)
                                ->where(DB::raw('MONTH(j.minggu_jadwal)'),'=',$bulannya)
                                ->groupBy('jr.nama_jenis_rab',DB::raw('MONTH(j.minggu_jadwal)'),'r.volume_rab_paket',DB::raw('r.volume_rab_paket * r.harga_rab_paket'))
                                ->get();
                            foreach ($jenis as $j) {
                                $kontrak=0;
                                $realisasi =0;
                                $bobot1=0;
                                $bobot2=0;
                                $sheet->setCellValue('A'.$baris, $awal);
                                $sheet->cell('A'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('center');
                                });
                                $sheet->mergeCells('B'.$baris.':E'.$baris);
                                $sheet->setCellValue('B'.$baris, $j->nama_jenis_rab);
                                $sheet->cell('B'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('left');
                                });
                                foreach ($detail as $d) {
                                    if ($j->nama_jenis_rab==$d->pekerjaan) {
                                        $kontrak += $d->kontrak;
                                        $realisasi+=$d->total_realisasi;
                                    }
                                }
                                $tot1 += $kontrak;
                                $sheet->setCellValue('F'.$baris, number_format($kontrak,2,",","."));
                                $sheet->cell('F'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $bobot1 = ($kontrak/$total->total)*100;
                                $tot2+=$bobot1;
                                $sheet->setCellValue('G'.$baris, $bobot1);
                                $sheet->cell('G'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $tot3+= $realisasi;
                                $sheet->mergeCells('H'.$baris.':I'.$baris);
                                $sheet->setCellValue('H'.$baris,number_format($realisasi,2,",",".") );
                                $sheet->cell('H'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $bobot2 = ($realisasi/$total->total)*100;
                                $tot4+=$bobot2;
                                $sheet->setCellValue('J'.$baris,$bobot2);
                                $sheet->cell('J'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $awal++;
                                $baris++;
                            }
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'JUMLAH');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');

                            });
                            $sheet->setCellValue('F'.$baris,number_format($kontrak,2,",",".") );
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,$tot2);
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($tot3,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,$tot4);
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(A) TOTAL PROGRESS BULAN INI');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($tot3,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $lalu =0;
                            $sheet->setCellValue('B'.$baris,'(B) TAGIHAN PROGRESS BULAN LALU');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $lalu =0;
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,'-');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(C) PROGRESS BULAN INI (A - B)');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $ini = $tot3-$lalu;
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($ini,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(D) DIBULATKAN');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($ini,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(E) PPN 10% (10/100XD)');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $ppn=$ini*(10/100);
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($ppn,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(F) JUMLAH YANG HARUS DIBAYARKAN s/d BULAN INI (D+E)');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $bayar= $ini+$ppn;
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($bayar,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $baris++;
                            $bar = $baris-1;
                            $sheet->mergeCells('A'.$bar.':B'.$baris);
                            $sheet->setCellValue('A'.$bar,'TERBILANG');
                            $sheet->cell('A'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('A'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('C'.$bar.':C'.$baris);
                            $sheet->setCellValue('C'.$bar,':');
                            $sheet->cell('C'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('C'.$bar)->getAlignment()->setWrapText(true);
                            $bilang = $this->terbilang($bayar);
                            $sheet->mergeCells('D'.$bar.':J'.$baris);
                            $sheet->setCellValue('D'.$bar,strtoupper($bilang));
                            $sheet->cell('D'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->getStyle('D'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->cell('A'.$bar.':J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $baris++;
                            $baris++;
                            $baris++;
                            $bar = $baris-2;
                            $bar1 = $baris-1;
                            $sheet->mergeCells('A'.$bar.':A'.$baris);
                            $sheet->setCellValue('A'.$bar,'');
                            $sheet->cell('A'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->getStyle('A'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('B'.$bar.':G'.$baris);
                            $sheet->setCellValue('B'.$bar,'PROSENTASE PELAKSANAAN PEKERJAAN / PROGRES');
                            $sheet->cell('B'.$bar, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('B'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('H'.$bar.':I'.$bar);
                            $sheet->setCellValue('H'.$bar,'S/D SAAT INI');
                            $sheet->cell('H'.$bar, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$bar1.':I'.$bar1);
                            $sheet->setCellValue('H'.$bar1,'BULAN LALU');
                            $sheet->cell('H'.$bar1, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,'BULAN INI');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('J'.$bar,$tot4);
                            $sheet->cell('J'.$bar, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$bar1,'-');
                            $sheet->cell('J'.$bar1, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,$tot4);
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $baris++;
                            $sheet->setCellValue('B'.$baris,'Diketahui/Disetujui,');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,'Diajukan Oleh,');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,'PEJABAT PEMBUAT KOMITMEN');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,'KONTRAKTOR PELAKSANA');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,'');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,strtoupper($paket->nama_kontraktor));
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $baris++;
                            $baris++;
                            $baris++;
                            $sheet->setCellValue('B'.$baris,$paket->nama_pegawai);
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,$paket->direktur_kontraktor);
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,$paket->pangkat);
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,'Direktur');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,$paket->nip_pegawai);
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            
                    }else{
                        $sheet->setCellValue('B13', 'KONTRAKTOR');
                        $sheet->setCellValue('C13', ':');
                        $sheet->setCellValue('D13', strtoupper($paket->nama_kontraktor));
                        $sheet->setCellValue('B14', 'NILAI KONTRAK');
                        $sheet->setCellValue('C14', ':');
                        $sheet->setCellValue('D14', 'Rp. '.number_format($paket->nilai_kontrak,2,",","."));
                        $sheet->cell('D13', function($cell){
                            $cell->setFontWeight('bold');
                        });
                        $sheet->cell('D14', function($cell){
                            $cell->setFontWeight('bold');
                        });
                        $sheet->setCellValue('F15', 'KONTRAKTOR');
                        $sheet->setCellValue('H15', ':');
                        $sheet->setCellValue('I15', 'APBD '.$paket->periode);
                        $sheet->setCellValue('F16', 'KODE REKENING');
                        $sheet->setCellValue('H16', ':');
                        $sheet->setCellValue('I16', $paket->id_kegiatan);
                        $sheet->setCellValue('F17', 'LAPORAN BULAN KE');
                        $sheet->setCellValue('H17', ':');
                        $sheet->setCellValue('I17', ' '.$inya.' '); 
                        $sheet->setCellValue('F18', 'TANGGAL');
                        $sheet->setCellValue('H18', ':');
                        $tgljadwal = DB::table('jadwal')
                                ->select(DB::raw('MIN(minggu_jadwal) as mulai'),DB::raw('MAX(minggu_jadwal) as selesai'))
                                ->where('id_paket','=',$paket->id_paket)
                                ->where(DB::raw('MONTH(minggu_jadwal)'),'=',$bulannya)
                                ->first();
                        $tgl1= date("d",strtotime($tgljadwal->mulai));
                        $tgl2='';
                        if (date("m",strtotime($tgljadwal->selesai))==1) {
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Januari '.date('Y',strtotime($tgljadwal->selesai));
                        } elseif (date("m",strtotime($tgljadwal->selesai))==2){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Februari '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==3){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Maret '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==4){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' April '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==5){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Mei '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==6){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Juni '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==7){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Juli '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==8){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Agustus '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==9){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' September '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==10){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Oktober '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==11){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' November '.date('Y',strtotime($tgljadwal->selesai));
                        }elseif (date("m",strtotime($tgljadwal->selesai))==12){
                            $tgl2 = date('d',strtotime($tgljadwal->selesai)).' Desember '.date('Y',strtotime($tgljadwal->selesai));
                        }
                        $sheet->setCellValue('I18', $tgl1.' s/d '.$tgl2 );
                        $jenis = DB::table('jenis_rab')->where('id_paket','=',$paket->id_paket)->get();
                        $sheet->mergeCells('A20:A21');
                        $sheet->setCellValue('A20', 'NO');
                        $sheet->cell('A20', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('A20')->getAlignment()->setWrapText(true);
                        $sheet->mergeCells('B20:E21');
                        $sheet->setCellValue('B20', 'URAIAN');
                        $sheet->cell('B20', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('B20')->getAlignment()->setWrapText(true);
                        $sheet->mergeCells('F20:G20');
                        $sheet->setCellValue('F20', 'KONTRAK');
                        $sheet->cell('F20', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('F20')->getAlignment()->setWrapText(true);
                        $sheet->setCellValue('F21', 'NILAI (Rp.)');
                        $sheet->cell('F21', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('F21')->getAlignment()->setWrapText(true);
                        $sheet->setCellValue('G21', 'BOBOT (%)');
                        $sheet->cell('G21', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('G21')->getAlignment()->setWrapText(true);
                        $sheet->mergeCells('H20:J20');
                        $sheet->setCellValue('H20', 'PEKERJAAN YANG DISELESAIKAN');
                        $sheet->cell('H20', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('H20')->getAlignment()->setWrapText(true);
                        $sheet->mergeCells('H21:I21');
                        $sheet->setCellValue('H21', 'NILAI (Rp.)');
                        $sheet->cell('H21', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('H21')->getAlignment()->setWrapText(true);
                        $sheet->setCellValue('J21', 'BOBOT (%)');
                        $sheet->cell('J21', function($cell){
                            $cell->setBorder('thin','thin','thin','thin');
                            $cell->setValignment('center');
                            $cell->setAlignment('center');
                        });
                        $sheet->getStyle('J21')->getAlignment()->setWrapText(true);
                        $sheet->cell('A4:J19', function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                        });
                        $baris = 22;
                        $awal = 'A';
                            $tot1=0;
                            $tot2=0;
                            $tot3=0;
                            $tot4=0;
                            $detail = DB::table('detail_rab AS d')
                                ->join('rab_paket AS r','d.id_rab_paket','=','r.id_rab_paket')
                                ->join('jenis_rab AS jr','r.id_jenis_rab','=','jr.id_jenis_rab')
                                ->join('paket AS p','jr.id_paket','=','p.id_paket')
                                ->join('jadwal AS j','d.minggu_ke','=','j.ke_jadwal')
                                ->select('jr.nama_jenis_rab AS pekerjaan',DB::raw('MONTH(j.minggu_jadwal) AS bulan'),DB::raw('r.volume_rab_paket * r.harga_rab_paket AS periode'),DB::raw('SUM(d.isi_detail_rab * r.harga_rab_paket) AS total_realisasi'))
                                ->where('p.id_paket','=',$paket->id_paket)
                                ->where('j.id_paket','=',$paket->id_paket)
                                ->where(DB::raw('MONTH(j.minggu_jadwal)'),'=',$bulannya)
                                ->groupBy('jr.nama_jenis_rab',DB::raw('MONTH(j.minggu_jadwal)'),'r.volume_rab_paket',DB::raw('r.volume_rab_paket * r.harga_rab_paket'))
                                ->get();
                            foreach ($jenis as $j) {
                                $kontrak=0;
                                $realisasi =0;
                                $bobot1=0;
                                $bobot2=0;
                                $sheet->setCellValue('A'.$baris, $awal);
                                $sheet->cell('A'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('center');
                                });
                                $sheet->mergeCells('B'.$baris.':E'.$baris);
                                $sheet->setCellValue('B'.$baris, $j->nama_jenis_rab);
                                $sheet->cell('B'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('left');
                                });
                                foreach ($detail as $d) {
                                    if ($j->nama_jenis_rab==$d->pekerjaan) {
                                        $kontrak += $d->kontrak;
                                        $realisasi+=$d->total_realisasi;
                                    }
                                }
                                $tot1 += $kontrak;
                                $sheet->setCellValue('F'.$baris, number_format($kontrak,2,",","."));
                                $sheet->cell('F'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $bobot1 = ($kontrak/$total->total)*100;
                                $tot2+=$bobot1;
                                $sheet->setCellValue('G'.$baris, $bobot1);
                                $sheet->cell('G'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $tot3+= $realisasi;
                                $sheet->mergeCells('H'.$baris.':I'.$baris);
                                $sheet->setCellValue('H'.$baris,number_format($realisasi,2,",",".") );
                                $sheet->cell('H'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $bobot2 = ($realisasi/$total->total)*100;
                                $tot4+=$bobot2;
                                $sheet->setCellValue('J'.$baris,$bobot2);
                                $sheet->cell('J'.$baris, function($cell){
                                    $cell->setBorder('thin','thin','thin','thin');
                                    $cell->setAlignment('right');
                                });
                                $awal++;
                                $baris++;
                            }
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'JUMLAH');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');

                            });
                            $sheet->setCellValue('F'.$baris,number_format($kontrak,2,",",".") );
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,$tot2);
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($tot3,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,$tot4);
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(A) TOTAL PROGRESS BULAN INI');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($tot3,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $lalu =0;
                            $sheet->setCellValue('B'.$baris,'(B) TAGIHAN PROGRESS BULAN LALU');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $lalu =0;
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,'-');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(C) PROGRESS BULAN INI (A - B)');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $ini = $tot3-$lalu;
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($ini,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(D) DIBULATKAN');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($ini,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(E) PPN 10% (10/100XD)');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $ppn=$ini*(10/100);
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($ppn,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $sheet->setCellValue('A'.$baris, '');
                            $sheet->cell('A'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('B'.$baris.':E'.$baris);
                            $sheet->setCellValue('B'.$baris,'(F) JUMLAH YANG HARUS DIBAYARKAN s/d BULAN INI (D+E)');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('left');
                            });
                            $sheet->setCellValue('F'.$baris,'');
                            $sheet->cell('F'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('G'.$baris,'');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $bayar= $ini+$ppn;
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,number_format($bayar,2,",",".") );
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,'');
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $baris++;
                            $bar = $baris-1;
                            $sheet->mergeCells('A'.$bar.':B'.$baris);
                            $sheet->setCellValue('A'.$bar,'TERBILANG');
                            $sheet->cell('A'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('A'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('C'.$bar.':C'.$baris);
                            $sheet->setCellValue('C'.$bar,':');
                            $sheet->cell('C'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('C'.$bar)->getAlignment()->setWrapText(true);
                            $bilang = $this->terbilang($bayar);
                            $sheet->mergeCells('D'.$bar.':J'.$baris);
                            $sheet->setCellValue('D'.$bar,strtoupper($bilang));
                            $sheet->cell('D'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->getStyle('D'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->cell('A'.$bar.':J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $baris++;
                            $baris++;
                            $baris++;
                            $bar = $baris-2;
                            $bar1 = $baris-1;
                            $sheet->mergeCells('A'.$bar.':A'.$baris);
                            $sheet->setCellValue('A'.$bar,'');
                            $sheet->cell('A'.$bar, function($cell){
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                                $cell->setBorder('thin','thin','thin','thin');
                            });
                            $sheet->getStyle('A'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('B'.$bar.':G'.$baris);
                            $sheet->setCellValue('B'.$bar,'PROSENTASE PELAKSANAAN PEKERJAAN / PROGRES');
                            $sheet->cell('B'.$bar, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                                $cell->setValignment('center');
                            });
                            $sheet->getStyle('B'.$bar)->getAlignment()->setWrapText(true);
                            $sheet->mergeCells('H'.$bar.':I'.$bar);
                            $sheet->setCellValue('H'.$bar,'S/D SAAT INI');
                            $sheet->cell('H'.$bar, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$bar1.':I'.$bar1);
                            $sheet->setCellValue('H'.$bar1,'BULAN LALU');
                            $sheet->cell('H'.$bar1, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('H'.$baris.':I'.$baris);
                            $sheet->setCellValue('H'.$baris,'BULAN INI');
                            $sheet->cell('H'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('center');
                            });
                            $sheet->setCellValue('J'.$bar,$tot4);
                            $sheet->cell('J'.$bar, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$bar1,'-');
                            $sheet->cell('J'.$bar1, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $sheet->setCellValue('J'.$baris,$tot4);
                            $sheet->cell('J'.$baris, function($cell){
                                $cell->setBorder('thin','thin','thin','thin');
                                $cell->setAlignment('right');
                            });
                            $baris++;
                            $baris++;
                            $sheet->setCellValue('B'.$baris,'Diketahui/Disetujui,');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,'Diajukan Oleh,');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,'PEJABAT PEMBUAT KOMITMEN');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,'KONTRAKTOR PELAKSANA');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,'');
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,strtoupper($paket->nama_kontraktor));
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $baris++;
                            $baris++;
                            $baris++;
                            $sheet->setCellValue('B'.$baris,$paket->nama_pegawai);
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,$paket->direktur_kontraktor);
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                                $cell->setFontWeight('bold');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,$paket->pangkat);
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $sheet->mergeCells('G'.$baris.':J'.$baris);
                            $sheet->setCellValue('G'.$baris,'Direktur');
                            $sheet->cell('G'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                            $baris++;
                            $sheet->setCellValue('B'.$baris,$paket->nip_pegawai);
                            $sheet->cell('B'.$baris, function($cell){
                                $cell->setAlignment('center');
                            });
                    }
                });
                $bulannya++;
            }
            
        })->export('xlsx');
    }
    public function kekata($x) {
        $x = abs($x);
        $angka = array("", "satu", "dua", "tiga", "empat", "lima",
        "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($x <12) {
            $temp = " ". $angka[$x];
        } else if ($x <20) {
            $temp = $this->kekata($x - 10). " belas";
        } else if ($x <100) {
            $temp = $this->kekata($x/10)." puluh". $this->kekata($x % 10);
        } else if ($x <200) {
            $temp = " seratus" . $this->kekata($x - 100);
        } else if ($x <1000) {
            $temp = $this->kekata($x/100) . " ratus" . $this->kekata($x % 100);
        } else if ($x <2000) {
            $temp = " seribu" . $this->kekata($x - 1000);
        } else if ($x <1000000) {
            $temp = $this->kekata($x/1000) . " ribu" . $this->kekata($x % 1000);
        } else if ($x <1000000000) {
            $temp = $this->kekata($x/1000000) . " juta" . $this->kekata($x % 1000000);
        } else if ($x <1000000000000) {
            $temp = $this->kekata($x/1000000000) . " milyar" . $this->kekata(fmod($x,1000000000));
        } else if ($x <1000000000000000) {
            $temp = $this->kekata($x/1000000000000) . " trilyun" . $this->kekata(fmod($x,1000000000000));
        }     
            return $temp;
    }
    public function terbilang($x, $style=3) {
        if($x<0) {
            $hasil = "minus ". trim($this->kekata($x));
        } else {
            $hasil = trim($this->kekata($x));
        }     
        switch ($style) {
            case 1:
                $hasil = strtoupper($hasil);
                break;
            case 2:
                $hasil = strtolower($hasil);
                break;
            case 3:
                $hasil = ucwords($hasil);
                break;
            default:
                $hasil = ucfirst($hasil);
                break;
        }     
        return $hasil;
    }
}
