<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cattle extends CI_Controller {

	public function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper('form');        
        $this->load->model('admin_model');
        define ('LANG', $this->admin_model->getLang());
        $this->lang->load('admin', LANG);
        if (empty($_SESSION['userInfo'][0]->id)) {
            redirect('login');
        }
    }

	public function index()
	{

        $data['page'] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->load->library('pagination');
        $config['base_url'] = site_url('/Cattle/index');
        $config['per_page'] = 10;
        $config["uri_segment"] = 3;
        $config["total_rows"] = $this->admin_model->countCattle();
        $choice = $config["total_rows"] / $config["per_page"];
        $config["num_links"] = floor($choice);
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $this->pagination->initialize($config);
        $this->data['sn']=$data['page'];
        $this->data['nav'] = $this->admin_model->getAllCattle($config["per_page"],$data['page']);
        $this->data['pagination'] = $this->pagination->create_links();
	$this->layout->view('admin/cattles', $this->data);
	}

	public function editCattle()
	{
		
		//Get cattle from database
		$this->data['menus'] = $this->admin_model->getCattle($this->uri->segment(3));
		//Load the view
		$this->layout->view('admin/cattle_edit', $this->data);
	}
        public function deleteImage($ci_id,$id)
	{
		//Get cattle from database
		$this->data['menus'] = $this->admin_model->deleteImage($ci_id);
		//redirect to view
                $success = "<strong> Image deleted Successfully !! </strong>";
                $this->session->set_flashdata('flashSuccess', $success);
		redirect('Cattle/editCattle/'.$id);
	}
        public function feature_listing($listing_id)
	{
		
		//Get cattle from database
		$status= $this->admin_model->feature_listing($listing_id);
		//redirect to view
                if($status){
                $this->admin_model->updateFeatureListing('cattle_listing',$listing_id,1);
                $success = "<strong>Product listed on feature listing section of home page successfully !! </strong>";
                }else{
                $this->admin_model->updateFeatureListing('cattle_listing',$listing_id,0);
                $success = "<strong>Product removed from feature listing section of home page successfully!! </strong>";
                }
                $this->session->set_flashdata('flashSuccess', $success);
		redirect('Cattle');
	}


	public function update()
	{
            

		//Load the form validation library
		$this->load->library('form_validation');

		$this->form_validation->set_rules('title', 'Title', 'trim|required');

		if($this->form_validation->run() == FALSE) {
		//Validation failed
			$this->editCattle();
		}
                else {

			//Validation passed
			$this->admin_model->updateCattle($this->uri->segment(3));
			//upload files 
                
                if(!empty($_FILES['image_url']['name'][0])){        
                $files = array();

		    if(empty($config))
		    {
		        $config['upload_path'] = '/home/ranchbuilder/public_html/assets/upload/cattle/';
		        $config['allowed_types'] = 'gif|jpg|jpeg|jpe|png';
		        $config['max_size']      = '800000000';
		    }

            $images = array();
            $this->load->library('upload');

            $files = $_FILES;
            $count = count($_FILES['image_url']['name']);


            for ($i = 0; $i < $count; $i++) {

                $_FILES['image_url']['name'] = $files['image_url']['name'][$i];
                $_FILES['image_url']['type'] = $files['image_url']['type'][$i];
                $_FILES['image_url']['tmp_name'] = $files['image_url']['tmp_name'][$i];
                $_FILES['image_url']['error'] = $files['image_url']['error'][$i];
                $_FILES['image_url']['size'] = $files['image_url']['size'][$i];

                $fileName = $_FILES['image_url']['name'];
                $images[] = $fileName;
                $config['file_name'] = $fileName;

                $this->upload->initialize($config);
                if($this->upload->do_upload('image_url')){
                   $imagename = $this->upload->data('file_name');
               
		            $image = array
					(
					'listing_id' =>$this->input->post('listing_id'),
					'image_url'  => $imagename
					);
		            $status  = $this->admin_model->insert_image($image);
                } else {
                   
                    echo $this->upload->display_errors('', '') . "\r";
                }

              }
            }
			$success = "<strong> Data Successfully Updated !! </strong>";
            $this->session->set_flashdata('flashSuccess',$success);
			redirect('/Cattle', 'refresh');
	  	}
	}


	function deleteCattle($id,$listing_id)
	{
		
		$this->admin_model->removeCattle($id,$listing_id);
		$success = "<strong> Cattle Successfully Deleted !! </strong>";
        $this->session->set_flashdata('flashSuccess', $success);
		redirect('/Cattle');
		
	}
}