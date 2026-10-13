<?php

class Upload extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->logged_in || $this->session->position !== 'admin') {
            show_error('Only administrators can upload files.', 403);
        }
    }

    public function vm_upload(){

        $page = "uploads";

            if(!file_exists(APPPATH.'views/pages/'.$page.'.php')){
                show_404();
            }

            $data['title'] = "Upload"; 

            $this->load->view('templates/header');
            $this->load->view('pages/'.$page, $data);
            $this->load->view('templates/footer');
    }
    
    public function upload_file(){
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            show_error('This operation requires a POST request.', 405);
        }

        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['upload_path'] = './uploads/';
        $config['max_size'] = 2048;
        $config['max_width'] = 3000;
        $config['max_height'] = 3000;
        $config['encrypt_name'] = TRUE;
        $config['file_ext_tolower'] = TRUE;
        $this->load->library('upload', $config);

        if($this->upload->do_upload('image')){
            $file = $this->upload->data();
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => true,
                'file_name' => $file['file_name'],
            )));
        }else{
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => strip_tags($this->upload->display_errors('', '')),
            )));
        }
    }





}

?>
