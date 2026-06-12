<?php defined('BASEPATH') or exit('No direct script access allowed');

class General_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Single Row
    |--------------------------------------------------------------------------
    */
    public function getOne($table, $where = [])
    {
        return $this->db
            ->where($where)
            ->get($table)
            ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Single Row Array
    |--------------------------------------------------------------------------
    */
    public function getRowArray($table, $where = [])
    {
        return $this->db
            ->where($where)
            ->get($table)
            ->row_array();
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Records (Object)
    |--------------------------------------------------------------------------
    */
    public function getAll($table, $where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db
            ->order_by('id', 'DESC')
            ->get($table)
            ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Records (Array)
    |--------------------------------------------------------------------------
    */
    public function getResultArray($table, $where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db
            ->order_by('id', 'DESC')
            ->get($table)
            ->result_array();
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Record
    |--------------------------------------------------------------------------
    */
    public function insert($table, $data)
    {
        $this->db->insert($table, $data);

        return $this->db->insert_id();
    }

    /*
    |--------------------------------------------------------------------------
    | Update Record
    |--------------------------------------------------------------------------
    */
    public function update($table, $where, $data)
    {
        return $this->db
            ->where($where)
            ->update($table, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Record
    |--------------------------------------------------------------------------
    */
    public function delete($table, $where)
    {
        return $this->db
            ->where($where)
            ->delete($table);
    }

    /*
    |--------------------------------------------------------------------------
    | Count Records
    |--------------------------------------------------------------------------
    */
    public function getCount($table, $where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->count_all_results($table);
    }

    /*
    |--------------------------------------------------------------------------
    | Check Exists
    |--------------------------------------------------------------------------
    */
    public function exists($table, $where = [])
    {
        return $this->db
            ->where($where)
            ->count_all_results($table) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Query Result Array
    |--------------------------------------------------------------------------
    */
    public function query($sql)
    {
        return $this->db->query($sql)->result_array();
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Query Row
    |--------------------------------------------------------------------------
    */
    public function queryRow($sql)
    {
        return $this->db->query($sql)->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Get By ID
    |--------------------------------------------------------------------------
    */
    public function getById($table, $id)
    {
        return $this->db
            ->where('id', $id)
            ->get($table)
            ->row();
    }
}
