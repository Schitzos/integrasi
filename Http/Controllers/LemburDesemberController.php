<?php namespace App\Http\Controllers;

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

class LemburDesemberController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->id_bidang==0) {
            $data = DB::table('lembur as l')
                ->join('kegiatan as k','l.kd_kegiatan','=','k.kd_kegiatan')
                ->join('pegawai as p','k.nip_pptk','=','p.nip_pegawai')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->where('l.periode','=',Auth::user()->periode)
                ->where('l.bulan','=','Desember')
                ->get();
        } else {
            $data = DB::table('lembur as l')
                ->join('kegiatan as k','l.kd_kegiatan','=','k.kd_kegiatan')
                ->join('pegawai as p','k.nip_pptk','=','p.nip_pegawai')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->where('l.periode','=',Auth::user()->periode)
                ->where('l.bulan','=','Desember')
                ->where('p.id_bidang','=',Auth::user()->id_bidang)
                ->get();
        }
        return view('lembur.indexdesember')->with('data',$data);
    }
    public function showdetail(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('detail_lembur')
                ->where('kd_lembur','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showabsensi(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('absensi_lembur')
                ->where('kd_lembur','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showpegawai(Request $request, $tgl1, $tgl2)
    {
        if ($request->ajax()) {
            if (Auth::user()->id_bidang==0) {
                $data = DB::table('pegawai')->whereNotIn('nip_pegawai', function($q) use($tgl1,$tgl2){
                    $q->select('pp.nip_pengikut')
                      ->from('pengikut_perjalanan AS pp')
                      ->join('perjalanan as p','pp.kode_perjalanan','=','p.kd_perjalanan')
                      ->whereBetween('p.tgl_berangkat', [$tgl1,$tgl2])
                      ->whereBetween('p.tgl_hrs_kembali', [$tgl1,$tgl2]);
                })
                ->where('nip_pegawai','<>',0)
                ->get();
            } else {
                $data = DB::table('pegawai')->whereNotIn('nip_pegawai', function($q) use($tgl1,$tgl2){
                    $q->select('pp.nip_pengikut')
                      ->from('pengikut_perjalanan AS pp')
                      ->join('perjalanan as p','pp.kode_perjalanan','=','p.kd_perjalanan')
                      ->whereBetween('p.tgl_berangkat', [$tgl1,$tgl2])
                      ->whereBetween('p.tgl_hrs_kembali', [$tgl1,$tgl2]);
                })
                ->where('nip_pegawai','<>',0)
                ->where('id_bidang','=',Auth::user()->id_bidang)
                ->get();
            }
            return Response::json($data);
        }
    }
    public function showgolongan(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('pegawai as p')
                ->join('golongan as g','p.id_golongan','=','g.id_golongan')
                ->where('p.nip_pegawai','=',$id)
                ->first();
            return Response::json($data);
        }
    }

    public function create()
    {
        if (Auth::user()->id_bidang==0) {
            $kegiatan = DB::table('kegiatan')->get();
        } else {
            $kegiatan = DB::table('kegiatan')->where('id_bidang','=',Auth::user()->id_bidang)->get();
        }
        $rekeknig = DB::table('rekening')->get();
        return view('lembur.createdesember')->with('kegiatan',$kegiatan)->with('rekeknig',$rekeknig);
    }

    public function store()
    {
        $rules = array(
            'Kegiatan'          => 'required',
            'Rekening'          => 'required',
            'Nomor_Surat'       => 'required',
            'JmlPegawai'        => 'integer'
        );
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.',
            'integer'  => 'Pegawai Harus Ditambahkan'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()){   
            return Redirect::to('/lembur/desember/create')->withErrors($validator)->withInput();
        }else{
            $JmlPegawai = input::get('JmlPegawai');
            DB::table('lembur')->insert(
            array( 
                'kd_kegiatan'       => Input::get('Kegiatan'),
                'kd_rekening'       => Input::get('Rekening'),
                'nomor_lembur'      => Input::get('Nomor_Surat'),
                'kantor'            => 'Dinas Pekerjaan Umum Kabupaten Gresik',
                'beban'             => 'APBD  Kabupaten Gresik ',
                'tgl_cetak_lembur'  => date('Y-m-d H:i:s'),
                'periode'           => Auth::user()->periode,
                'bulan'             => 'Desember'
            ));
            $lembur = DB::table('lembur')->select(DB::raw('MAX(kd_lembur) AS kd_lembur'))->first();
            for ($i=1; $i <= $JmlPegawai ; $i++) {
                $tglabsen = explode(',',Input::get('Tgl_Lembur_'.$i));
                DB::table('detail_lembur')->insert(
                array( 
                    'kd_lembur'         => $lembur->kd_lembur,
                    'nip_pegawai'       => Input::get('Pegawai_'.$i),
                    'jabatan'           => Input::get('Jabatan_'.$i),
                    'pekerjaan'         => Input::get('Pekerjaan_'.$i),
                    'uang_lembur_lembur'=> Input::get('Uang_Lembu_'.$i),
                    'uang_makan_lembur' => Input::get('Uang_Mak_'.$i),
                    'volume'            => Input::get('Volume_'.$i),
                    'lama_lembur'       => count($tglabsen)
                ));
                for($j=0; $j < count($tglabsen) ; $j++) { 
                    $tgl= explode(' ',$tglabsen[$j]);
                    if ($tgl[1]=='Desember') {
                        $tgl[1]='12';
                    }
                    DB::table('absensi_lembur')->insert(
                    array( 
                        'kd_lembur'     => $lembur->kd_lembur,
                        'nip_pegawai'   => Input::get('Pegawai_'.$i),
                        'tgl_absensi'   => $tgl[2].'-'.$tgl[1].'-'.$tgl[0]
                    ));
                }
            }
            $textnya = preg_replace( "/\r|\n/", "",Input::get('Hasil'));
            $hasil = explode('.',$textnya);
            for ($i=0; $i < count($hasil)-1; $i++) { 
                DB::table('hasil_lembur')->insert(
                array( 
                    'kd_lembur'         => $lembur->kd_lembur,
                    'uraian_hasil'      => substr($hasil[$i],1)
                ));
            }
            DB::table('hasil_lembur')->where('kd_lembur', '=',$lembur->kd_lembur)->where('uraian_hasil','=',0)->delete();
            Session::flash('message', 'Data Lembur berhasil ditambahkan');
            return Redirect::to('/lembur/desember');
        }
    }
    public function edit($id)
    {
        $data =  DB::table('lembur')->where('kd_lembur','=',$id)->first();
        $jmlpegawai = DB::table('detail_lembur')->select(DB::raw('COUNT(*) AS jml'))->where('kd_lembur','=',$id)->first();
        if (Auth::user()->id_bidang==0) {
            $kegiatan = DB::table('kegiatan')->get();
            $pegawai = DB::table('pegawai')->where('nip_pegawai','<>',0)->get();
        } else {
            $kegiatan = DB::table('kegiatan')->where('id_bidang','=',Auth::user()->id_bidang)->get();
            $pegawai = DB::table('pegawai')->where('nip_pegawai','<>',0)->where('id_bidang','=',Auth::user()->id_bidang)->get();
        }
        $rekeknig = DB::table('rekening')->get();
        $hasil =  DB::table('hasil_lembur')->where('kd_lembur','=',$id)->get();
        return view('lembur.editdesember')->with('data',$data)->with('pegawai',$pegawai)->with('kegiatan',$kegiatan)
        ->with('rekeknig',$rekeknig)->with('jmlpegawai',$jmlpegawai)->with('hasil',$hasil);
    }
    public function update()
    {
        $rules = array(
            'Kegiatan'          => 'required',
            'Rekening'          => 'required',
            'Nomor_Surat'       => 'required',
            'JmlPegawai'        => 'integer'
        );
        $messages = array(
            'required'  => 'Kolom :attribute harus di isi.'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/lembur/desember/'.$id.'/edit')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('IdLembur');
            $JmlPegawai = input::get('JmlPegawai');
            DB::table('lembur')->where('kd_lembur',$id)->update(
                array(   
                    'kd_kegiatan'       => Input::get('Kegiatan'),
                    'kd_rekening'       => Input::get('Rekening'),
                    'nomor_lembur'      => Input::get('Nomor_Surat'),
                    'kantor'            => 'Dinas Pekerjaan Umum Kabupaten Gresik',
                    'beban'             => 'APBD  Kabupaten Gresik ',
                )
            );
            DB::table('absensi_lembur')->where('kd_lembur', '=',$id)->delete();
            DB::table('detail_lembur')->where('kd_lembur', '=',$id)->delete();
            DB::table('hasil_lembur')->where('kd_lembur', '=',$id)->delete();
            for ($i=1; $i <= $JmlPegawai ; $i++) { 
                $tglabsen = explode(',',Input::get('Tgl_Lembur_'.$i));
                DB::table('detail_lembur')->insert(
                array( 
                    'kd_lembur'         => $id,
                    'nip_pegawai'       => Input::get('Pegawai_'.$i),
                    'jabatan'           => Input::get('Jabatan_'.$i),
                    'pekerjaan'         => Input::get('Pekerjaan_'.$i),
                    'uang_lembur_lembur'=> Input::get('Uang_Lembu_'.$i),
                    'uang_makan_lembur' => Input::get('Uang_Mak_'.$i),
                    'volume'            => Input::get('Volume_'.$i),
                    'lama_lembur'       => count($tglabsen)
                ));
                
                for($j=0; $j < count($tglabsen) ; $j++) { 
                    $tgl= explode(' ',$tglabsen[$j]);
                    if ($tgl[1]=='Desember') {
                        $tgl[1]='12';
                    }
                    DB::table('absensi_lembur')->insert(
                    array( 
                        'kd_lembur'     => $id,
                        'nip_pegawai'   => Input::get('Pegawai_'.$i),
                        'tgl_absensi'   => $tgl[2].'-'.$tgl[1].'-'.$tgl[0]
                    ));
                }
            }
            $textnya = preg_replace( '/\r|\n/', '',Input::get('Hasil'));
            $hasil = explode('.',$textnya);
            for ($i=0; $i < count($hasil); $i++) { 
                DB::table('hasil_lembur')->insert(
                array( 
                    'kd_lembur'         => $id,
                    'uraian_hasil'      => substr($hasil[$i],1)
                ));
            }
            DB::table('hasil_lembur')->where('kd_lembur', '=',$id)->where('uraian_hasil','=',0)->delete();
            Session::flash('message', 'Data Lembur berhasil diubah');
            return Redirect::to('/lembur/desember');
        }       
    }
    public function cetak($id)
    {
        $data =DB::table('lembur as l')
            ->join('rekening as r','l.kd_rekening','=','r.kd_rekening')
            ->join('kegiatan as k','l.kd_kegiatan','=','k.kd_kegiatan')
            ->join('pegawai as p','k.nip_pptk','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->join('bidang as b','k.id_bidang','=','b.id_bidang')
            ->where('l.kd_lembur','=',$id)->first();
        $detail = DB::table('detail_lembur as d')
            ->join('pegawai as p','d.nip_pegawai','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->where('d.kd_lembur','=',$id)
            ->get();
        $ppk =  DB::table('pegawai as p')
            ->join('jabatan_user as j','p.id_jabatan','=','j.id_jabatan')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->where('j.nama_jabatan','like','PPK%')
            ->where('p.id_bidang','=',$data->id_bidang)
            ->first();
        $hasil = DB::table('hasil_lembur')->where('kd_lembur','=',$id)->get();
        $absensi = DB::table('absensi_lembur')->where('kd_lembur','=',$id)->where('nip_pegawai','=',$detail[0]->nip_pegawai)->get();
        $jmllembur = $detail[0]->lama_lembur;
        $view = View::make('lembur.cetak', array('hasil' => $hasil, 'jmllembur' => $jmllembur, 'data' => $data, 'detail' => $detail, 'absensi' => $absensi, 'ppk' => $ppk, 'i' => 0))->render(); 
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper([0, 0, 609.449, 935.433], 'potrait');
        $pdf->loadHTML($view);
        return $pdf->stream();
    }
    public function cetakmiring($id)
    {
        $data =DB::table('lembur as l')
            ->join('rekening as r','l.kd_rekening','=','r.kd_rekening')
            ->join('kegiatan as k','l.kd_kegiatan','=','k.kd_kegiatan')
            ->join('pegawai as p','k.nip_pptk','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->join('bidang as b','k.id_bidang','=','b.id_bidang')
            ->where('l.kd_lembur','=',$id)->first();
        $detail = DB::table('detail_lembur as d')
            ->join('pegawai as p','d.nip_pegawai','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->where('d.kd_lembur','=',$id)
            ->get();
        $absensi = DB::table('absensi_lembur')->where('kd_lembur','=',$id)->get();
        $bendahara  = DB::table('pegawai as p')
            ->join('jabatan_user as j','p.id_jabatan','=','j.id_jabatan')
            ->where('j.nama_jabatan','like','Bendahara%')
            ->first();
        $ppk =  DB::table('pegawai as p')
            ->join('jabatan_user as j','p.id_jabatan','=','j.id_jabatan')
            ->where('j.nama_jabatan','like','PPK%')
            ->first();
        $view = View::make('lembur.cetakmiring', array('data' => $data, 'detail' => $detail, 'absensi' => $absensi, 
            'bendahara' => $bendahara, 'ppk' => $ppk, 'i' => 0))->render(); 
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper([0, 0, 609.449, 935.433], 'landscape');
        $pdf->loadHTML($view);
        return $pdf->stream();
    }
    public function destroy($id)
    {
        DB::table('absensi_lembur')->where('kd_lembur', '=',$id)->delete();
        DB::table('detail_lembur')->where('kd_lembur', '=',$id)->delete();
        DB::table('hasil_lembur')->where('kd_lembur', '=',$id)->delete();
        DB::table('lembur')->where('kd_lembur', '=',$id)->delete();
        Session::flash('message', 'Data Lembur berhasil dihapus !');
        return Redirect::to('/lembur/desember');
    }

    public function hapuspegawai($id,$nip)
    {
        DB::table('absensi_lembur')->where('kd_lembur', '=',$id)->where('nip_pegawai', '=',$nip)->delete();
        DB::table('detail_lembur')->where('kd_lembur', '=',$id)->where('nip_pegawai', '=',$nip)->delete();
        Session::flash('message', 'Data lembur Pegawai berhasil dihapus !');
    }

}
