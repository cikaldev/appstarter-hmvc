<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Librari ini dibuat untuk mempermudah pembuatan laporan PDF
 * pada Framework Codeigniter 3.x dengan meng-Extend DomPDF.
 *
 * @package    CodeIgniter
 * @subpackage libraries
 * @category   library
 * @version    1.2
 * @since      2016
 * @author     iancikal <cikaldev@gmail.com>
 * @license    MIT License
 */

use Dompdf\Dompdf;

class Pdf extends Dompdf
{
  
  protected $ci;
  public $filename;
  public $download;

  public function __construct()
  {
    parent::__construct();
    $this->ci =& get_instance();
    $this->filename = 'report.pdf';
    $this->download = false;
  }

  public function load_view($view, $data=[], $orientation='portrait')
  {
    $html = $this->ci->load->view($view, $data, true);
    $this->load_html($html);
    $this->setPaper('A4', $orientation);
    $this->render();
    return $this->stream($this->filename, array('Attachment' => $this->download));
  }

  public function load_save($view, $data=[])
  {
    $html = $this->ci->load->view($view, $data, true);
    $this->load_html($html);
    $this->setPaper('A4', 'portrait');
    $this->render();
    $output = $this->output();
    file_put_contents('./reports/'.$this->filename, $output);
  }

}

/* End of file Pdf.php */
/* Location: ./application/libraries/Pdf.php */
