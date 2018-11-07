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


class PSatuController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id,$ik,$is){
    	if (Auth::user()->admin==1) {
    		if ($id==0) {
	    		if ($ik==0) {
					if($is==0){
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
								->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
								->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
								->get();
						$jmlpaket = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select(DB::raw('COUNT(*) AS jml'))
							->first();
						$bulane="";
					}else{
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('d.bulan_setuju','=',$is)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('d.bulan_setuju','=',$is)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
								->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
								->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
								->where('d.bulan_setuju','=',$is)
								->get();
						$jmlpaket = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select(DB::raw('COUNT(*) AS jml'))
							->where('d.bulan_setuju','=',$is)
							->first();
						$bulane=$is;
					}
					$kegiatanne ="";
	    		} else {
					if($is==0){
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
							'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
							'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
							DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->get();
						$bulane="";
					}else{
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
							'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
							'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
							DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->get();
						$bulane=$is;
					}
	    			$kegiatanne=$ik;
	    		}
	    		$judule = "Semua Bidang";
	    		$bidange="";
	    		$kegiatan  = DB::table('kegiatan')->get();
	    	} else {
	    		if ($ik==0) {
					if($is==0){
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->get();
						$bulane="";
					}else{
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('d.bulan_setuju','=',$is)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('d.bulan_setuju','=',$is)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('d.bulan_setuju','=',$is)
							->get();
						$bulane=$is;
					}
		    		$kegiatanne="";
	    		} else {
					if($is==0){
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->get();
						$bulane="";
					}else{
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->where('d.bulan_setuju','=',$is)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->get();
						$bulane=$is;
					}
		    		$kegiatanne=$ik;
	    		}
	    		$bid = DB::table('bidang')->where('id_bidang','=',$id)->first();
	    		$judule = $bid->nama_bidang;
	    		$bidange = $bid->id_bidang;
				$kegiatan  = DB::table('kegiatan as k')
						->join('seksi as s','k.id_seksi','=','s.id_seksi')
		            	->join('bidang as b','s.id_bidang','=','b.id_bidang')
		                ->where('b.id_bidang','=',$id)
		                ->get();
	    	}
			$bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
			$bulane=$is;
    	} else {
			if ($id==0) {
	    		if ($ik==0) {
					if($is==0){
						if(Auth::user()->pptk==1){
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}else{
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}
						if(Auth::user()->pptk==1){
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}else {
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}
						if(Auth::user()->pptk==1){
							$paket = DB::table('dpa as d')
									->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
									->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
									->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
									->join('program as g','k.id_program','=','g.id_program')
									->join('seksi as s','k.id_seksi','=','s.id_seksi')
									->join('bidang as b','s.id_bidang','=','b.id_bidang')
									->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
									'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
									'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
									DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
									->where('k.pptk','=',Auth::user()->nip_pegawai)
									->get();
						}else{
							$paket = DB::table('dpa as d')
									->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
									->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
									->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
									->join('program as g','k.id_program','=','g.id_program')
									->join('seksi as s','k.id_seksi','=','s.id_seksi')
									->join('bidang as b','s.id_bidang','=','b.id_bidang')
									->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
									'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
									'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
									DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
									->where('k.ppk','=',Auth::user()->nip_pegawai)
									->get();
						}
						if(Auth::user()->pptk==1){
							$jmlpaket = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select(DB::raw('COUNT(*) AS jml'))
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->first();
						}else{
							$jmlpaket = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->select(DB::raw('COUNT(*) AS jml'))
								->first();
						}
						$bulane="";
					}else{
						if(Auth::user()->pptk==1){
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}else{
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}
						if(Auth::user()->pptk==1){
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}else {
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}
						if(Auth::user()->pptk==1){
							$paket = DB::table('dpa as d')
									->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
									->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
									->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
									->join('program as g','k.id_program','=','g.id_program')
									->join('seksi as s','k.id_seksi','=','s.id_seksi')
									->join('bidang as b','s.id_bidang','=','b.id_bidang')
									->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
									'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
									'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
									DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
									->where('k.pptk','=',Auth::user()->nip_pegawai)
									->get();
						}else{
							$paket = DB::table('dpa as d')
									->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
									->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
									->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
									->join('program as g','k.id_program','=','g.id_program')
									->join('seksi as s','k.id_seksi','=','s.id_seksi')
									->join('bidang as b','s.id_bidang','=','b.id_bidang')
									->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
									'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
									'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
									DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
									->where('k.ppk','=',Auth::user()->nip_pegawai)
									->get();
						}
						if(Auth::user()->pptk==1){
							$jmlpaket = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select(DB::raw('COUNT(*) AS jml'))
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->first();
						}else{
							$jmlpaket = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->select(DB::raw('COUNT(*) AS jml'))
								->first();
						}
						$bulane=$is;
					}
	    			$kegiatanne ="";
	    		} else {
					if($is==0){
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
							'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
							'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
							DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->get();
						$bulane="";
					}else{
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
							'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
							'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
							DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('k.id_kegiatan','=',$ik)
							->get();
						$bulane=$is;
					}
		    		$kegiatanne=$ik;
	    		}
	    		$judule = "Semua Bidang";
	    		$bidange="";
	    		$kegiatan  = DB::table('kegiatan')->get();
	    	} else {
	    		if ($ik==0) {
					if($is==0){
						if(Auth::user()->pptk==1){
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}else{
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}
						if(Auth::user()->pptk==1){
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}else{
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}
						if(Auth::user()->pptk==1){
							$paket = DB::table('dpa as d')
								->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
								->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
									'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
									'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
									DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->get();
						}else{
							$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('b.id_bidang','=',$id)
							->get();
						}
						$bulane="";
					}else{
						if(Auth::user()->pptk==1){
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}else{
							$program = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('g.id_program','g.nama_program')
								->get();
						}
						if(Auth::user()->pptk==1){
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.pptk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}else{
							$datkeg = DB::table('dpa as d')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
									DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
									DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->where('k.ppk','=',Auth::user()->nip_pegawai)
								->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
								->get();
						}
						if(Auth::user()->pptk==1){
							$paket = DB::table('dpa as d')
								->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
								->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
								->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
								->join('program as g','k.id_program','=','g.id_program')
								->join('seksi as s','k.id_seksi','=','s.id_seksi')
								->join('bidang as b','s.id_bidang','=','b.id_bidang')
								->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
									'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
									'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
									DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
								->where('b.id_bidang','=',$id)
								->get();
						}else{
							$paket = DB::table('dpa as d')
							->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
							->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
								'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
								'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
								DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('b.id_bidang','=',$id)
							->get();
						}
						$bulane=$is;
					}
		    		$kegiatanne="";
	    		} else {
					if($is==0){
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
						->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
						->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
						->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
						->join('program as g','k.id_program','=','g.id_program')
						->join('seksi as s','k.id_seksi','=','s.id_seksi')
						->join('bidang as b','s.id_bidang','=','b.id_bidang')
						->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
							'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
							'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
							DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
						->where('b.id_bidang','=',$id)
		    			->where('k.id_kegiatan','=',$ik)
		    			->get();
						$bulane="";
					}else{
						$program = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('g.id_program','g.nama_program',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->groupBy('g.id_program','g.nama_program')
							->get();
						$datkeg = DB::table('dpa as d')
							->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
							->join('program as g','k.id_program','=','g.id_program')
							->join('seksi as s','k.id_seksi','=','s.id_seksi')
							->join('bidang as b','s.id_bidang','=','b.id_bidang')
							->select('k.id_program','k.id_kegiatan','k.nama_kegiatan',DB::raw('SUM(d.nilai)  AS pagu'),
								DB::raw('SUM(CASE WHEN d.paket=1 THEN d.nilai_kontrak ELSE 0 END) AS nilai_kontrak'),
								DB::raw('SUM(CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END) as progres_keuangan'))
							->where('b.id_bidang','=',$id)
							->where('k.id_kegiatan','=',$ik)
							->groupBy('k.id_program','k.id_kegiatan','k.nama_kegiatan')
							->get();
						$paket = DB::table('dpa as d')
						->leftJoin('konsultan as u','d.id_konsultan','=','u.id_konsultan')
						->leftJoin('kontraktor as c','d.id_kontraktor','=','c.id_kontraktor')
						->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
						->join('program as g','k.id_program','=','g.id_program')
						->join('seksi as s','k.id_seksi','=','s.id_seksi')
						->join('bidang as b','s.id_bidang','=','b.id_bidang')
						->select('k.id_kegiatan','d.rekening','d.uraian','d.mulai','d.selesai',
							'u.nama_konsultan','c.nama_kontraktor','d.nomor_kontrak',
							'd.nilai as pagu','d.nilai_kontrak','d.progres_fisik',
							DB::raw('CASE WHEN d.oke_oce =1 THEN d.progres_keuangan ELSE 0 END as progres_keuangan'))
						->where('b.id_bidang','=',$id)
		    			->where('k.id_kegiatan','=',$ik)
		    			->get();
						$bulane=$is;
					}
						
		    		$kegiatanne=$ik;
	    		}
	    		$bid = DB::table('bidang')->where('id_bidang','=',$id)->first();
	    		$judule = $bid->nama_bidang;
	    		$bidange = $bid->id_bidang;
				$kegiatan  = DB::table('kegiatan as k')
						->join('seksi as s','k.id_seksi','=','s.id_seksi')
		            	->join('bidang as b','s.id_bidang','=','b.id_bidang')
		                ->where('b.id_bidang','=',$id)
		                ->get();
	    	}
	    	$bidang = DB::table('bidang')->where('id_bidang','<>',0)->get();
		}
		$jadwal = DB::table('jadwal')->get();
		$bulan = DB::table('bulan')->get();
    	return view ('psatu.psatu_list')->with('program',$program)->with('datkeg',$datkeg)->with('paket',$paket)->with('bidang',$bidang)->with('judule',$judule)
			->with('kegiatan',$kegiatan)->with('bidange',$bidange)->with('kegiatanne',$kegiatanne)->with('bulan',$bulan)
			->with('bulane',$bulane);
    }
}
