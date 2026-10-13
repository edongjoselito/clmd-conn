<?php


class OneView extends CI_Controller
{


     public function authors()
     {
          $this->load->view('pages/authors');
     }

     public function about()
     {
          $this->load->view('pages/about');
     }
}
