<?php

/**
 * Admin Template Codeigniter 4
 * Author	: Agus Prawoto Hadi
 * Website	: https://jagowebdev.com
 * Year		: 2021
 */

namespace App\Controllers;

use App\Models\ChartjsModel;
use App\Models\CalegModel;
use App\Models\KecamatanModel;
use App\Models\KelurahanModel;
use App\Models\LihatpemilihModel;
use App\Models\RdppDptModel;

class Lihat_pemilih extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->model = new ChartjsModel;
        $this->modCaleg = new CalegModel;
        ;
        $this->modKec = new KecamatanModel;
        $this->modKel = new KelurahanModel;
        $this->modelLP = new LihatpemilihModel;
        $this->modTps = new RdppDptModel;
        
        $this->addJs($this->config->baseURL . 'public/vendors/chartjs/Chart.bundle.min.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/chartjs/Chart.min.css');

        $this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.bootstrap.js');
        $this->addJs($this->config->baseURL . 'public/themes/modern/js/lihat-pemilih.js');

        $this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
        $this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2-bootstrap4.min.css');
    }

    public function index() {

        $list_tahun = [2019, 2020, 2021];

        $tahun = '';
        if (empty($_GET['tahun'])) {
            $tahun = 2021;
        }

        if (!empty($_GET['tahun']) && in_array($_GET['tahun'], $list_tahun)) {
            $tahun = $_GET['tahun'];
        }

        $this->data['penjualan'] = $this->model->getPenjualan($tahun);
//        print_r($this->data['penjualan']); exit;
        $this->data['item_terjual'] = $this->model->getItemTerjual($tahun);
        $this->data['tahun'] = $tahun;

//        $this->data['caleg'] = $this->modCaleg->getViewCalegByIdUser($this->session->get('user')['id_user']);
        $this->data['caleg'] = $this->modCaleg->getCalegByIdUser($this->session->get('user')['id_user']);
//        $this->data['relawan'] = $this->modRelawan->getViewRelawanByIdUser($this->session->get('user')['id_user']);
        $dapil = $this->data['caleg']['id_dapil'];

        $qKec = $this->modKec->getKecamatan(" where dapil like '%,$dapil]' or dapil like '[$dapil,%' or dapil like '%,$dapil,%'");

//        $query = $this->modProv->getProvinsi(" where id = 41863");
        $this->data['kec'][''] = '';
        foreach ($qKec as $key => $val) {
            $this->data['kec'][$val['id']] = $val['nama'];
        }

        if (!empty($_GET['id_kel'])) {
//            $relawan = $this->modelLP->getPemilihKelurahanPerCaleg($this->data['caleg']['id_prov'], $this->data['caleg']['id_kab'], $_GET['id_kel'], $this->session->get('user')['id_user'], $dapil);

            $trcc = $this->modelLP->getTotalPemilihTpsPercaleg($this->session->get('user')['id_user'], $_GET['id_kel']);
            $total = 0;
            foreach ($trcc as $key => $value) {
                $jml[$value['noTps']] = $value['total'];
                $total = $total + $value['total'];
            }
            
            $qTps = $this->modTps->getTpsRdppDpt($this->data['caleg']['id_prov'], $this->data['caleg']['id_kab'], " where idKel = ".$_GET['id_kel']);

            $relawan[0] = array();
//            print_r($relawan[0]); exit;
            foreach ($qTps as $key => $value) {
                $dt['noTps'] = $value['noTps'];
                $dt['wilayah'] = $value['noTps'];

                if (isset($jml[$value['noTps']])) {
                    $dt['total'] = $jml[$value['noTps']];
                }
                else {
                    $dt['total'] = 0;
                }
                
                $relawan[0][] = $dt;
            }
            
            $relawan[1] = $this->modelLP->getTotalPemilihPerkelurahan($this->data['caleg']['id_prov'], $this->data['caleg']['id_kab'], $_GET['id_kel'], $total);
            
            $this->data['relawan'] = $relawan[0];
            $this->data['total_tps'] = $relawan[1]['total_tps'];
            $this->data['capaian'] = $relawan[1]['capaian'];
            
            $kel = $this->modKel->getKelurahanById($_GET['id_kel']);
            $this->data['label'] = "Kelurahan ".$kel['nama'];
        } elseif (!empty($_GET['id_kec'])) {
//            $relawan = $this->modelLP->getPemilihKecamatanPerCaleg($this->data['caleg']['id_prov'], $this->data['caleg']['id_kab'], $_GET['id_kec'], $this->session->get('user')['id_user'], $dapil);

            $trcc = $this->modelLP->getTotalPemilihPerkelurahanPercaleg($this->session->get('user')['id_user'], $_GET['id_kec']);
            $total = 0;
            foreach ($trcc as $key => $value) {
                $jml[$value['id_kel']] = $value['total'];
                $total = $total + $value['total'];
            }
            
            $qKel = $this->modKel->getKelurahan(" where parent_id = ".$_GET['id_kec']);

            $relawan[0] = array();
            foreach ($qKel as $key => $value) {
                $dt['id'] = $value['id'];
                $dt['wilayah'] = $value['nama'];

                if (isset($jml[$value['id']]))
                    $dt['total'] = $jml[$value['id']];
                else {
                    $dt['total'] = 0;
                }
                
                $relawan[0][] = $dt;
            }
            
            $relawan[1] = $this->modelLP->getTotalPemilihPerkecamatan($this->data['caleg']['id_prov'], $this->data['caleg']['id_kab'], $_GET['id_kec'], $total);
            
            $this->data['relawan'] = $relawan[0];
            $this->data['total_tps'] = $relawan[1]['total_tps'];
            $this->data['capaian'] = $relawan[1]['capaian'];
            
            $kec = $this->modKec->getKecamatanById($_GET['id_kec']);
            $this->data['label'] = "Kecamatan ".$kec['nama'];
        } else {
//            $relawan = $this->modelLP->getPemilihKabupatenPerCaleg($this->data['caleg']['id_prov'], $this->data['caleg']['id_kab'], $this->session->get('user')['id_user'], $dapil);

            $trcc = $this->modelLP->getTotalPemilihPerkecamatanPercaleg($this->session->get('user')['id_user']);
            $total = 0;
            foreach ($trcc as $key => $value) {
                $jml[$value['id_kec']] = $value['total'];
                $total = $total + $value['total'];
            }

            $relawan[0] = array();
            foreach ($qKec as $key => $value) {
                $dt['id'] = $value['id'];
                $dt['wilayah'] = $value['nama'];

                if (isset($jml[$value['id']]))
                    $dt['total'] = $jml[$value['id']];
                else {
                    $dt['total'] = 0;
                }
                
                $relawan[0][] = $dt;
            }
            
            $relawan[1] = $this->modelLP->getTotalPemilihPerdapil($this->data['caleg']['id_prov'], $this->data['caleg']['id_kab'], $dapil, $total);
            
            $this->data['relawan'] = $relawan[0];
            $this->data['total_tps'] = $relawan[1]['total_tps'];
            $this->data['capaian'] = $relawan[1]['capaian'];
            
            $this->data['label'] = "Kota/Kabupaten ".$this->data['caleg']['kabupaten'];
            
//            print_r($this->data['relawan']); exit;
        }

        $this->data['message']['status'] = 'ok';
        if (empty($this->data['penjualan'])) {
            $this->data['message']['status'] = 'error';
            $this->data['message']['message'] = 'Data tidak ditemukan';
        }

        $this->view('lihat-pemilih.php', $this->data);
    }

    public function getDataKel() {
        $filterid = $_GET['filterid'];

        if (!$filterid) {
            $filterid = '0';
        }

        $items = $this->modKel->getKelurahan(" where parent_id = $filterid");

        $html = '';

        $select = '<option value=\"\"></option>';
        $html .= '$("select#noTps").html("' . $select . '");';
        $html .= '$("select#idDpt").html("' . $select . '");';
//        $html .= '$("#jmltps").val(0);';
//        $html .= '$("#butuhsuara").val(0);';

        foreach ($items as $list) :
            $select .= "<option value='" . $list['id'] . "'>" . $list['nama'] . "</option>";
        endforeach;

        $html .= '$("select#id_kel").html("' . $select . '");';
//        $html .= '$("input[name=\'' . $csrf_token['name'] . '\']").val("' . $_COOKIE[$csrf_token['name']] . '");';
        echo $html;
    }

}
