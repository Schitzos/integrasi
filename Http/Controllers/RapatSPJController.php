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

class RapatController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->id_bidang==0) {
            $data = DB::table('honor as h')
                ->join('kegiatan as k','h.kd_kegiatan','=','k.kd_kegiatan')
                ->join('rekening as r','h.kd_rekening','=','r.kd_rekening')
                ->join('pegawai as p','k.nip_pptk','=','p.nip_pegawai')
                ->where('h.periode_honor','=',Auth::user()->periode)
                ->where('h.tipe_honor','=',2)
                ->get();
        } else {
            $data = DB::table('honor as h')
                ->join('kegiatan as k','h.kd_kegiatan','=','k.kd_kegiatan')
                ->join('rekening as r','h.kd_rekening','=','r.kd_rekening')
                ->join('pegawai as p','k.nip_pptk','=','p.nip_pegawai')
                ->where('h.periode_honor','=',Auth::user()->periode)
                ->where('h.tipe_honor','=',2)
                ->where('p.id_bidang','=',Auth::user()->id_bidang)
                ->get();
        }
        return view('rapat.index')->with('data',$data);
    }
    public function showdetail(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('detail_honor as d')
                ->join('pegawai as p','d.nip_pegawai_honor','=','p.nip_pegawai')
                ->where('d.kd_honor','=',$id)
                ->get();
            return Response::json($data);
        }
    }
    public function showpegawai(Request $request, $id)
    {
        if ($request->ajax()) {
            if (Auth::user()->id_bidang==0) {
                $data = DB::table('pegawai as p')
                    ->join('bidang as b','p.id_bidang','=','b.id_bidang')
                    ->join('jabatan_user as j','p.id_jabatan','=','j.id_jabatan')
                    ->join('kegiatan as k','b.id_bidang','=','k.id_bidang')
                    ->where('k.kd_kegiatan','=',$id)
                    ->get();
            } else {
                $data = DB::table('pegawai')->where('id_bidang','=',Auth::user()->id_bidang)->get();
            }
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
        return view('rapat.create')->with('kegiatan',$kegiatan)->with('rekeknig',$rekeknig);
    }

    public function store()
    {
        $rules = array(
            'Kegiatan'          => 'required',
            'Rekening'          => 'required',
            'Tgl_Rapat'         => 'required',
            'JmlPegawai'        => 'integer'
        );
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.',
            'integer'  => 'Pegawai Harus Ditambahkan'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()){   
            return Redirect::to('/rapat/create')->withErrors($validator)->withInput();
        }else{
            $JmlPegawai = input::get('JmlPegawai');
            if (date('m', strtotime(Input::get('Tgl_Rapat')))==1) {
                $bulan = 'Januari';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==2) {
                $bulan = 'Februari';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==3) {
                $bulan = 'Maret';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==4) {
                $bulan = 'April';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==5) {
                $bulan = 'Mei';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==6) {
                $bulan = 'Juni';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==7) {
                $bulan = 'Juli';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==8) {
                $bulan = 'Agustus';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==9) {
                $bulan = 'September';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==10) {
                $bulan = 'Oktober';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==11) {
                $bulan = 'November';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==12) {
                $bulan = 'Desember';
            }
            DB::table('honor')->insert(
            array( 
                'kd_kegiatan'       => Input::get('Kegiatan'),
                'kd_rekening'       => Input::get('Rekening'),
                'kantor_honor'      => 'Dinas Pekerjaan Umum Kabupaten Gresik',
                'beban_honor'       => 'APBD  Kabupaten Gresik ',
                'tgl_cetak_honor'   => date('Y-m-d', strtotime(Input::get('Tgl_Rapat'))),
                'periode_honor'     => Auth::user()->periode,
                'tipe_honor'        => 2,
                'bulan_honor'       => $bulan
            ));
            $honor = DB::table('honor')->select(DB::raw('MAX(kd_honor) AS kd_honor'))->first();
            for ($i=1; $i <= $JmlPegawai ; $i++) {
                DB::table('detail_honor')->insert(
                array( 
                    'kd_honor'          => $honor->kd_honor,
                    'nip_pegawai_honor' => Input::get('Pegawai_'.$i),
                    'uang_honor'        => Input::get('Hon_'.$i)
                ));
            }
            Session::flash('message', 'Data Honor Rapat berhasil ditambahkan');
            return Redirect::to('/rapat');
        }
    }
    public function edit($id)
    {
        $data =  DB::table('honor')->where('kd_honor','=',$id)->first();
        $jmlpegawai = DB::table('detail_honor')->select(DB::raw('COUNT(*) AS jml'))->where('kd_honor','=',$id)->first();
        if (Auth::user()->id_bidang==0) {
            $kegiatan = DB::table('kegiatan')->get();
            $pegawai = DB::table('pegawai')->where('nip_pegawai','<>',0)->get();
        } else {
            $kegiatan = DB::table('kegiatan')->where('id_bidang','=',Auth::user()->id_bidang)->get();
            $pegawai = DB::table('pegawai')->where('nip_pegawai','<>',0)->where('id_bidang','=',Auth::user()->id_bidang)->get();
        }
        $rekening = DB::table('rekening')->get();
        return view('rapat.edit')->with('data',$data)->with('pegawai',$pegawai)->with('kegiatan',$kegiatan)
        ->with('rekening',$rekening)->with('jmlpegawai',$jmlpegawai);
    }
    public function update()
    {
        $rules = array(
            'Kegiatan'          => 'required',
            'Rekening'          => 'required',
            'Tgl_Rapat'         => 'required',
            'JmlPegawai'        => 'integer'
        );
        $messages = array(
            'required' => 'Kolom :attribute harus di isi.',
            'integer'  => 'Pegawai Harus Ditambahkan'
        );
        $validator = Validator::make(Input::all(), $rules,$messages);
        if ($validator->fails()) {   
            return Redirect::to('/rapat/'.$id.'/edit')->withErrors($validator)->withInput();
        } else { 
            $id = Input::get('IdHonor');
            $JmlPegawai = input::get('JmlPegawai');
            if (date('m', strtotime(Input::get('Tgl_Rapat')))==1) {
                $bulan = 'Januari';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==2) {
                $bulan = 'Februari';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==3) {
                $bulan = 'Maret';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==4) {
                $bulan = 'April';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==5) {
                $bulan = 'Mei';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==6) {
                $bulan = 'Juni';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==7) {
                $bulan = 'Juli';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==8) {
                $bulan = 'Agustus';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==9) {
                $bulan = 'September';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==10) {
                $bulan = 'Oktober';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==11) {
                $bulan = 'November';
            } else if (date('m', strtotime(Input::get('Tgl_Rapat')))==12) {
                $bulan = 'Desember';
            }
            DB::table('honor')->where('kd_honor',$id)->update(
                array(   
                    'kd_kegiatan'       => Input::get('Kegiatan'),
                    'kd_rekening'       => Input::get('Rekening'),
                    'kantor_honor'      => 'Dinas Pekerjaan Umum Kabupaten Gresik',
                    'beban_honor'       => 'APBD  Kabupaten Gresik ',
                    'tgl_cetak_honor'   => date('Y-m-d', strtotime(Input::get('Tgl_Rapat'))),
                    'periode_honor'     => Auth::user()->periode,
                    'tipe_honor'        => 2,
                    'bulan_honor'       => $bulan
                )
            );
            DB::table('detail_honor')->where('kd_honor', '=',$id)->delete();
            for ($i=1; $i <= $JmlPegawai ; $i++) { 
                DB::table('detail_honor')->insert(
                array( 
                    'kd_honor'          => $id,
                    'nip_pegawai_honor' => Input::get('Pegawai_'.$i),
                    'uang_honor'        => Input::get('Hon_'.$i)
                ));
            }
            DB::table('detail_honor')->where('kd_honor', '=',$id)->where('uang_honor','=',0)->delete();
            Session::flash('message', 'Data Honor Rapat berhasil diubah');
            return Redirect::to('/rapat');
        }       
    }
    public function cetak($id)
    {
        $data =DB::table('honor as h')
            ->join('rekening as r','h.kd_rekening','=','r.kd_rekening')
            ->join('kegiatan as k','h.kd_kegiatan','=','k.kd_kegiatan')
            ->join('pegawai as p','k.nip_pptk','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->join('bidang as b','k.id_bidang','=','b.id_bidang')
            ->where('h.kd_honor','=',$id)->first();
        $detail = DB::table('detail_honor as d')
            ->join('pegawai as p','d.nip_pegawai_honor','=','p.nip_pegawai')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->where('d.kd_honor','=',$id)
            ->get();
        $bendahara  = DB::table('pegawai as p')
            ->join('jabatan_user as j','p.id_jabatan','=','j.id_jabatan')
            ->where('j.nama_jabatan','like','Bendahara%')
            ->first();
        $ppk =  DB::table('pegawai as p')
            ->join('jabatan_user as j','p.id_jabatan','=','j.id_jabatan')
            ->join('golongan as g','p.id_golongan','=','g.id_golongan')
            ->where('j.nama_jabatan','like','PPK%')
            ->where('p.id_bidang','=',$data->id_bidang)
            ->first();
        $view = View::make('rapat.cetak', array('data' => $data, 'detail' => $detail, 'bendahara' => $bendahara, 'ppk' => $ppk, 'i' => 0))->render(); 
        $pdf = App::make('dompdf.wrapper');
        $paper_orientation = 'landscape';
        $pdf->setpaper('legal',$paper_orientation);
        $pdf->loadHTML($view);
        return $pdf->stream();
    }
    public function destroy($id)
    {
        DB::table('detail_honor')->where('kd_honor', '=',$id)->delete();
        DB::table('honor')->where('kd_honor', '=',$id)->delete();
        Session::flash('message', 'Data Honor Rapat berhasil dihapus !');
        return Redirect::to('/rapat');
    }

    public function hapuspegawai($id,$nip)
    {
        DB::table('absensi_lembur')->where('kd_lembur', '=',$id)->where('nip_pegawai', '=',$nip)->delete();
        DB::table('detail_lembur')->where('kd_lembur', '=',$id)->where('nip_pegawai', '=',$nip)->delete();
        Session::flash('message', 'Data Pegawai Honor berhasil dihapus !');
    }

}
