<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('is_login')) {
  function is_login() {
    $CI =& get_instance();
    return (bool)($CI->session->userdata('id') && $CI->session->userdata('logged_in'));
  }
}

if (!function_exists('script_tag')) {
  function script_tag($file, $type='text/javascript') {
    return '<script type="'.$type.'" src="'.base_url($file).'"></script>';
  }
}

if (!function_exists('notif')) {
  function notif($type, $message) {
    get_instance()->session->set_flashdata('msg', '<script>toastr.'.$type.'("'.$message.'")</script>');
  }
}

if (!function_exists('kode_otomatis')) {
  function kode_otomatis($prefix, $tabel, $kolom, $len=5) {
    $max = get_instance()->db->select_max($kolom)->get($tabel)->row($kolom);
    $num = preg_replace("/[^0-9]/", "", $max);
    return sprintf("%s%0".$len."s", strtoupper($prefix), (int)$num + 1);
  }
}

/* End of file custom_helper.php */
/* Location: ./application/helpers/custom_helper.php */
