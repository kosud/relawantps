<?php

namespace App\Models;

class KabupatenModel extends \App\Models\BaseModel {

    public function __construct() {
        parent::__construct();
    }

    public function getKabupaten($where) {
        $sql = 'SELECT * FROM wil_kab' . $where;
        $result = $this->dbpemilu->query($sql)->getResultArray();
        return $result;
    }
    
    public function getKabupatenPemilih() {
        $sql = 'SELECT id_kab FROM pemilih group by id_kab';
        $result = $this->db->query($sql)->getResultArray();
        $aProv = array();
        foreach ($result as $key => $value) {
            $aProv[] = $value['id_kab'];
        }
        $prov = implode(',', $aProv);
        
        $result = $this->getKabupaten(" where id in ($prov)");
        
        return $result;
    }

    public function getKabupatenById($id) {
        $sql = 'SELECT * FROM wil_kab WHERE id = ?';
        $result = $this->dbpemilu->query($sql, $id)->getRowArray();
        return $result;
    }

    public function saveData($id) {
        $data_db['nama'] = $_POST['nama_produk'];
        $data_db['deskripsi_produk'] = $_POST['deskripsi_produk'];
        $data_db['id_user_input'] = $this->session->get('user')['id_user'];
        $id_produk = $id;

        $builder = $this->db->table('Provinsi');
        if (empty($id)) {
            $builder->insert($data_db);
            $id_produk = $this->db->insertID();
        } else {
            $builder->update($data_db, ['id' => $_POST['id']]);
        }

        return ['query' => $this->db->error(), 'id' => $id_produk];
    }

    public function deleteProvinsiById($id) {
        $delete = $this->db->table('Provinsi')->delete(['id' => $id]);
        // $delete = true;
        return $delete;
    }

}
