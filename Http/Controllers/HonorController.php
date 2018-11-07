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
use Response;

class HonorController extends Controller
{
    public function index_pphp(){
        $data = DB::table('honor_pphp as hp')
        ->join('kegiatan as keg','keg.id_kegiatan','hp.id_kegiatan')
        ->get();
        $kegiatan  = DB::table('dpa as d')
            ->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->where('d.paket','=',7)
            ->get();
        $bulan  = DB::table('bulan')->get();
        return view ('honor.pphp')
        ->with('data', $data)
        ->with('kegiatan', $kegiatan)
        ->with('bulan', $bulan);
    }
    public function add_pphp()
    {
        DB::table('honor_pphp')->insert(
            array(   
                'id_kegiatan'      => Input::get('pphp_keg'),
                'sts_kendali'      => 0

            )
        );
        Session::flash('message', 'Data Honor PPHP berhasil ditambahkan');
        return Redirect::to('/honor/pphp');
    }
    public function edit_pphp(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('honor_pphp')->where('id_pphp', $id)->first();
            return Response::json($data);
        }
    }
    public function update_pphp()
    {
        $id = Input::get('idhnrPphp');
        DB::table('honor_pphp')->where('id_pphp',$id)->update(
            array(   
                'id_kegiatan'      => Input::get('pphp_eh_keg'),
                'sts_kendali'      => 0
            )
        );
        Session::flash('message', 'Data Honor PPHP berhasil disimpan');
        return Redirect::to('/honor/pphp');
    }
    public function delete_pphp($id)
    {
        DB::table('list_h_pphp')->where('id_h_pphp',$id)->delete();
        DB::table('honor_pphp')->where('id_pphp',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/pphp');
    }
    public function list_h_pphp($id){
        $data = DB::table('list_h_pphp')
        ->join('pegawai','list_h_pphp.nip_pegawai','pegawai.nip_pegawai')
        ->where('id_h_pphp',$id)->get();
        $idnya = DB::table('honor_pphp')->where('id_pphp',$id)->select('id_pphp')->first();
        $pegawai = DB::table('pegawai')->where('pphp',1)->get();
        return view ('honor.list_h_pphp')
        ->with('data',$data)
        ->with('idnya',$idnya)
        ->with('pegawai',$pegawai);
    }
    public function add_list_h_pphp()
    {
        DB::table('list_h_pphp')->insert(
            array(   
                'nip_pegawai'   => Input::get('nip_peg'),
                'honor'         => Input::get('nil_pphp_hnr'),
                'id_h_pphp'     => Input::get('idHpphp')
            )
        );
        Session::flash('message', 'Data berhasil disimpan');
        return Redirect::to('/honor/pphp/list/'.Input::get('idHpphp'));
    }
    public function edit_hnr_pphp(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('list_h_pphp')->where('id_l_pphp', $id)->first();
            return Response::json($data);
        }
    }
    public function update_list_h_pphp()
    {
        $id = Input::get('idPPHP');
        DB::table('list_h_pphp')->where('id_l_pphp',$id)->update(
            array(   
                'nip_pegawai'   => Input::get('hnr_nip_peg'),
                'honor'         => Input::get('hnr_nil_pphp_hnr')
            )
        );
        Session::flash('message', 'Data list PPHP berhasil dirubah');
        return Redirect::to('/honor/pphp/list/'.Input::get('hnr_idHpphp'));
    }
    public function cetak_h_pphp()
    {
        $id_keg = Input::get('idkegiatan1');
        $idpphp = Input::get('idPPHP');
        $kodeHonor = Input::get('kd_honor');
        $bln = DB::table('bulan')->where('kode_bulan',Input::get('pil_bln'))->first();
        $kegiatan = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.pptk')
        ->where('id_kegiatan',$id_keg)->first();
        $ppk = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.ppk')
        ->where('id_kegiatan',$id_keg)->select('pegawai.nip_pegawai','pegawai.nama_pegawai')->first();
        $bendahara = DB::table('pegawai')->where('bendahara', 1)->select('nip_pegawai','nama_pegawai')->first();
        $list = DB::table('list_h_pphp')
        ->join('pegawai','pegawai.nip_pegawai','list_h_pphp.nip_pegawai')
        ->join('golongan','pegawai.id_golongan','golongan.id_golongan')
        ->where('id_h_pphp', $idpphp)->get();
        return view ('honor.cetak_hnr')
        ->with('bulan',$bln)
        ->with('kodeHonor',$kodeHonor)
        ->with('ppk',$ppk)
        ->with('list',$list)
        ->with('bendahara',$bendahara)
        ->with('kegiatan',$kegiatan);
    }
    public function delete_l_pphp($id, $idpphp)
    {
        DB::table('list_h_pphp')->where('id_l_pphp',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/pphp/list/'.$idpphp);
    }
    public function index_ppb(){
        $data = DB::table('honor_ppb as hp')
        ->join('kegiatan as keg','keg.id_kegiatan','hp.id_kegiatan')
        ->get();
        $kegiatan  = DB::table('dpa as d')->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->where('d.paket','=',8)->get();
        $bulan  = DB::table('bulan')->get();
        return view ('honor.ppb')
        ->with('data', $data)
        ->with('kegiatan', $kegiatan)
        ->with('bulan', $bulan);
    }
    public function add_ppb()
    {
        DB::table('honor_ppb')->insert(
            array(   
                'id_kegiatan'      => Input::get('ppb_keg'),
                'sts_kendali' => 0
            )
        );
        Session::flash('message', 'Data Honor PPB berhasil ditambahkan');
        return Redirect::to('/honor/ppb');
    }
    public function edit_ppb(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('honor_ppb')->where('id_ppb', $id)->first();
            return Response::json($data);
        }
    }
    public function update_ppb()
    {
        $id = Input::get('idhnrPpb');
        DB::table('honor_ppb')->where('id_ppb',$id)->update(
            array(   
                'id_kegiatan'      => Input::get('ppb_eh_keg'),
                'sts_kendali'   => 0
            )
        );
        Session::flash('message', 'Data Honor PPB berhasil disimpan');
        return Redirect::to('/honor/ppb');
    }
    public function delete_ppb($id)
    {
        DB::table('list_h_ppb')->where('id_h_ppb',$id)->delete();
        DB::table('honor_ppb')->where('id_ppb',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/ppb');
    }
    public function list_h_ppb($id){
        $data = DB::table('list_h_ppb')
        ->join('pegawai','list_h_ppb.nip_pegawai','pegawai.nip_pegawai')
        ->where('id_h_ppb',$id)->get();
        $idnya = DB::table('honor_ppb')->where('id_ppb',$id)->select('id_ppb')->first();
        $pegawai = DB::table('pegawai')->where('ppbj',1)->get();
        return view ('honor.list_h_ppb')
        ->with('data',$data)
        ->with('idnya',$idnya)
        ->with('pegawai',$pegawai);
    }
    public function add_list_h_ppb()
    {
        DB::table('list_h_ppb')->insert(
            array(   
                'nip_pegawai'   => Input::get('nip_peg'),
                'honor'         => Input::get('nil_ppb_hnr'),
                'id_h_ppb'     => Input::get('idHppb')
            )
        );
        Session::flash('message', 'Data berhasil disimpan');
        return Redirect::to('/honor/ppb/list/'.Input::get('idHppb'));
    }
    public function edit_hnr_ppb(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('list_h_ppb')->where('id_l_ppb', $id)->first();
            return Response::json($data);
        }
    }
    public function update_list_h_ppb()
    {
        $id = Input::get('idPPB');
        DB::table('list_h_ppb')->where('id_l_ppb',$id)->update(
            array(   
                'nip_pegawai'   => Input::get('hnr_nip_peg'),
                'honor'         => Input::get('hnr_nil_ppb_hnr')
            )
        );
        Session::flash('message', 'Data list PPB berhasil dirubah');
        return Redirect::to('/honor/ppb/list/'.Input::get('hnr_idHppb'));
    }
    public function cetak_h_ppb()
    {
        $id_keg = Input::get('idkegiatan1');
        $idppb = Input::get('idPPB');
        $kodeHonor = Input::get('kd_honor');
        $bln = DB::table('bulan')->where('kode_bulan',Input::get('pil_bln'))->first();
        $kegiatan = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.pptk')
        ->where('id_kegiatan',$id_keg)->first();
        $ppk = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.ppk')
        ->where('id_kegiatan',$id_keg)->select('pegawai.nip_pegawai','pegawai.nama_pegawai')->first();
        $bendahara = DB::table('pegawai')->where('bendahara', 1)->select('nip_pegawai','nama_pegawai')->first();
        $list = DB::table('list_h_ppb')
        ->join('pegawai','pegawai.nip_pegawai','list_h_ppb.nip_pegawai')
        ->join('golongan','pegawai.id_golongan','golongan.id_golongan')
        ->where('id_h_ppb', $idppb)->get();
        return view ('honor.cetak_hnr')
        ->with('bulan',$bln)
        ->with('kodeHonor',$kodeHonor)
        ->with('ppk',$ppk)
        ->with('list',$list)
        ->with('bendahara',$bendahara)
        ->with('kegiatan',$kegiatan);
    }
    public function delete_l_ppb($id, $idppb)
    {
        DB::table('list_h_ppb')->where('id_l_ppb',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/ppb/list/'.$idppb);
    }


    public function index_bulanan(){
        $data = DB::table('honor_bulanan as hp')
        ->join('kegiatan as keg','keg.id_kegiatan','hp.id_kegiatan')
        ->get();
        $kegiatan  = DB::table('dpa as d')->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->where('d.paket','=',10)->get();
        $bulan  = DB::table('bulan')->get();
        return view ('honor.bulanan')
        ->with('data', $data)
        ->with('kegiatan', $kegiatan)
        ->with('bulan', $bulan);
    }
    public function add_bulanan()
    {
        DB::table('honor_bulanan')->insert(
            array(   
                'id_kegiatan'      => Input::get('bulanan_keg')
            )
        );
        Session::flash('message', 'Data Honor BULANAN berhasil ditambahkan');
        return Redirect::to('/honor/bulanan');
    }
    public function edit_bulanan(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('honor_bulanan')->where('id_bulanan', $id)->first();
            return Response::json($data);
        }
    }
    public function update_bulanan()
    {
        $id = Input::get('idhnrBulanan');
        DB::table('honor_bulanan')->where('id_bulanan',$id)->update(
            array(   
                'id_kegiatan'      => Input::get('bulanan_eh_keg')
            )
        );
        Session::flash('message', 'Data Honor BULANAN berhasil disimpan');
        return Redirect::to('/honor/bulanan');
    }
    public function delete_bulanan($id)
    {
        DB::table('list_h_bulanan')->where('id_h_bulanan',$id)->delete();
        DB::table('honor_bulanan')->where('id_bulanan',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/bulanan');
    }
    public function list_h_bulanan($id){
        $data = DB::table('list_h_bulanan')
        ->join('pegawai','list_h_bulanan.nip_pegawai','pegawai.nip_pegawai')
        ->where('id_h_bulanan',$id)->get();
        $idnya = DB::table('honor_bulanan')->where('id_bulanan',$id)->select('id_bulanan')->first();
        $pegawai = DB::table('pegawai')->get();
        return view ('honor.list_h_bulanan')
        ->with('data',$data)
        ->with('idnya',$idnya)
        ->with('pegawai',$pegawai);
    }
    public function add_list_h_bulanan()
    {
        DB::table('list_h_bulanan')->insert(
            array(   
                'nip_pegawai'   => Input::get('nip_peg'),
                'honor'         => Input::get('nil_bulanan_hnr'),
                'id_h_bulanan'     => Input::get('idHbulanan')
            )
        );
        Session::flash('message', 'Data berhasil disimpan');
        return Redirect::to('/honor/bulanan/list/'.Input::get('idHbulanan'));
    }
    public function edit_hnr_bulanan(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('list_h_bulanan')->where('id_l_bulanan', $id)->first();
            return Response::json($data);
        }
    }
    public function update_list_h_bulanan()
    {
        $id = Input::get('idBULANAN');
        DB::table('list_h_bulanan')->where('id_l_bulanan',$id)->update(
            array(   
                'nip_pegawai'   => Input::get('hnr_nip_peg'),
                'honor'         => Input::get('hnr_nil_bulanan_hnr')
            )
        );
        Session::flash('message', 'Data list BULANAN berhasil dirubah');
        return Redirect::to('/honor/bulanan/list/'.Input::get('hnr_idHbulanan'));
    }
    public function cetak_h_bulanan()
    {
        $id_keg = Input::get('idkegiatan1');
        $idbulanan = Input::get('idBULANAN');
        $kodeHonor = Input::get('kd_honor');
        $bln = DB::table('bulan')->where('kode_bulan',Input::get('pil_bln'))->first();
        $kegiatan = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.pptk')
        ->where('id_kegiatan',$id_keg)->first();
        $ppk = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.ppk')
        ->where('id_kegiatan',$id_keg)->select('pegawai.nip_pegawai','pegawai.nama_pegawai')->first();
        $bendahara = DB::table('pegawai')->where('bendahara', 1)->select('nip_pegawai','nama_pegawai')->first();
        $list = DB::table('list_h_bulanan')
        ->join('pegawai','pegawai.nip_pegawai','list_h_bulanan.nip_pegawai')
        ->join('golongan','pegawai.id_golongan','golongan.id_golongan')
        ->where('id_h_bulanan', $idbulanan)->get();
        return view ('honor.cetak_hnr')
        ->with('bulan',$bln)
        ->with('kodeHonor',$kodeHonor)
        ->with('ppk',$ppk)
        ->with('list',$list)
        ->with('bendahara',$bendahara)
        ->with('kegiatan',$kegiatan);
    }
    public function delete_l_bulanan($id, $idbulanan)
    {
        DB::table('list_h_bulanan')->where('id_l_bulanan',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/bulanan/list/'.$idbulanan);
    }

    public function index_timteknis(){
        $data = DB::table('honor_timteknis as hp')
        ->join('kegiatan as keg','keg.id_kegiatan','hp.id_kegiatan')
        ->get();
        $kegiatan  = DB::table('dpa as d')->join('kegiatan as k','d.id_kegiatan','=','k.id_kegiatan')
            ->where('d.paket','=',9)->get();
        $bulan  = DB::table('bulan')->get();
        return view ('honor.timteknis')
        ->with('data', $data)
        ->with('kegiatan', $kegiatan)
        ->with('bulan', $bulan);
    }
    public function add_timteknis()
    {
        DB::table('honor_timteknis')->insert(
            array(   
                'id_kegiatan'      => Input::get('timteknis_keg')
            )
        );
        Session::flash('message', 'Data Honor TIMTEKNIS berhasil ditambahkan');
        return Redirect::to('/honor/timteknis');
    }
    public function edit_timteknis(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('honor_timteknis')->where('id_timteknis', $id)->first();
            return Response::json($data);
        }
    }
    public function update_timteknis()
    {
        $id = Input::get('idhnrTimteknis');
        DB::table('honor_timteknis')->where('id_timteknis',$id)->update(
            array(   
                'id_kegiatan'      => Input::get('timteknis_eh_keg')
            )
        );
        Session::flash('message', 'Data Honor TIMTEKNIS berhasil disimpan');
        return Redirect::to('/honor/timteknis');
    }
    public function delete_timteknis($id)
    {
        DB::table('list_h_timteknis')->where('id_h_timteknis',$id)->delete();
        DB::table('honor_timteknis')->where('id_timteknis',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/timteknis');
    }
    public function list_h_timteknis($id){
        $data = DB::table('list_h_timteknis')
        ->join('pegawai','list_h_timteknis.nip_pegawai','pegawai.nip_pegawai')
        ->where('id_h_timteknis',$id)->get();
        $idnya = DB::table('honor_timteknis')->where('id_timteknis',$id)->select('id_timteknis')->first();
        $pegawai = DB::table('pegawai')->get();
        return view ('honor.list_h_timteknis')
        ->with('data',$data)
        ->with('idnya',$idnya)
        ->with('pegawai',$pegawai);
    }
    public function add_list_h_timteknis()
    {
        DB::table('list_h_timteknis')->insert(
            array(   
                'nip_pegawai'   => Input::get('nip_peg'),
                'honor'         => Input::get('nil_timteknis_hnr'),
                'id_h_timteknis'     => Input::get('idHtimteknis')
            )
        );
        Session::flash('message', 'Data berhasil disimpan');
        return Redirect::to('/honor/timteknis/list/'.Input::get('idHtimteknis'));
    }
    public function edit_hnr_timteknis(Request $request, $id){
        if ($request->ajax()) {
            $data = DB::table('list_h_timteknis')->where('id_l_timteknis', $id)->first();
            return Response::json($data);
        }
    }
    public function update_list_h_timteknis()
    {
        $id = Input::get('idTIMTEKNIS');
        DB::table('list_h_timteknis')->where('id_l_timteknis',$id)->update(
            array(   
                'nip_pegawai'   => Input::get('hnr_nip_peg'),
                'honor'         => Input::get('hnr_nil_timteknis_hnr')
            )
        );
        Session::flash('message', 'Data list TIMTEKNIS berhasil dirubah');
        return Redirect::to('/honor/timteknis/list/'.Input::get('hnr_idHtimteknis'));
    }
    public function cetak_h_timteknis()
    {
        $id_keg = Input::get('idkegiatan1');
        $idtimteknis = Input::get('idTIMTEKNIS');
        $kodeHonor = Input::get('kd_honor');
        $bln = DB::table('bulan')->where('kode_bulan',Input::get('pil_bln'))->first();
        $kegiatan = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.pptk')
        ->where('id_kegiatan',$id_keg)->first();
        $ppk = DB::table('kegiatan')
        ->join('pegawai','pegawai.nip_pegawai','kegiatan.ppk')
        ->where('id_kegiatan',$id_keg)->select('pegawai.nip_pegawai','pegawai.nama_pegawai')->first();
        $bendahara = DB::table('pegawai')->where('bendahara', 1)->select('nip_pegawai','nama_pegawai')->first();
        $list = DB::table('list_h_timteknis')
        ->join('pegawai','pegawai.nip_pegawai','list_h_timteknis.nip_pegawai')
        ->join('golongan','pegawai.id_golongan','golongan.id_golongan')
        ->where('id_h_timteknis', $idtimteknis)->get();
        return view ('honor.cetak_hnr_timteknis')
        ->with('bulan',$bln)
        ->with('kodeHonor',$kodeHonor)
        ->with('ppk',$ppk)
        ->with('list',$list)
        ->with('bendahara',$bendahara)
        ->with('kegiatan',$kegiatan);
    }
    public function delete_l_timteknis($id, $idtimteknis)
    {
        DB::table('list_h_timteknis')->where('id_l_timteknis',$id)->delete();
        Session::flash('message', 'Data berhasil dihapus');
        return Redirect::to('/honor/timteknis/list/'.$idtimteknis);
    }
}
