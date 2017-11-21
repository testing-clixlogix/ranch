<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Emails extends CI_Controller {

	public function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper('form'); 
        $this->load->helper('my_admin_helper'); 
        $this->load->model('admin_model');
        define ('LANG', $this->admin_model->getLang());
        $this->lang->load('admin', LANG);
        if (empty($_SESSION['userInfo'][0]->id)) {
            redirect('login');
        }
    }

	public function index()
	{
 		$this->load->library('pagination');
		$result_per_page =15;  // the number of result per page
		$config['base_url'] = site_url(). '/pages/';
		$config['total_rows'] = $this->admin_model->countEmails();
		$config['per_page'] = $result_per_page;
		$this->pagination->initialize($config);
		//Get pages from database
		$this->data['pages'] = $this->admin_model->getEmails($result_per_page, $this->uri->segment(3));

		//Load the view
		$this->layout->view('admin/emailTemplates', $this->data);
	}

	public function addPage()
	{
		//Load the view
		$this->data['templates'] = 0 ;
		$this->layout->view('admin/page_new', $this->data);
	}

	public function confirm()
	{
		//Load the form validation library
		$this->load->library('form_validation');
		//Set validation rules
		$this->form_validation->set_rules('pageURL', 'page URL', 'trim|alpha_dash|required|is_unique[hoosk_page_attributes.pageURL]');
		$this->form_validation->set_rules('pageTitle', 'page title', 'trim|required');
		$this->form_validation->set_rules('navTitle', 'navigation title', 'trim|required');

		if($this->form_validation->run() == FALSE) {
			//Validation failed
			$this->addPage();
		}  else  {
			//Validation passed
			//Add the page
			$this->load->library('Sioen');
			$this->admin_model->createPage();
			//Return to page list
			redirect('/pages', 'refresh');
	  	}
	}

	public function editEmail()
	{
		//Get page details from database
		$this->data['pages'] = $this->admin_model->getEmail($this->uri->segment(3));
		//Load the view
		$this->layout->view('admin/email_edit', $this->data);
	}
        
        public function templateContent()
	{
                $temp_id=$this->input->post('template_id');
		//Get page details from database
		$templatedetails= $this->admin_model->getPageTemplateById($temp_id);
                $attributes=explode(',',$templatedetails[0]['attributes']);
                $message="";

                foreach($attributes as $attr){
                 $message.= "<div class='form-group'>
                 <label>".$attr."</label>
                 <input type='text' name='".$attr."' class='form-control' />
                 </div>";
                }
	   
             echo $message;
		
	}

	public function edited()
	{
                $data=$this->input->post();
               
		$this->load->library('form_validation');
		//Set validation rules
		
		$this->form_validation->set_rules('type', 'type', 'trim|required');

		if($this->form_validation->run() == FALSE) {
			//Validation failed
			$this->editEmail();
		}  else  {
			//Validation passed
			//Update the email template
			
			$this->admin_model->updateEmail($this->uri->segment(3));
			//Return to email template list
			$success = "<strong> Email Template Updated Successfully !! </strong>";
            $this->session->set_flashdata('flashSuccess', $success);
			redirect('/emails', 'refresh');
	  	}
	}

	
	function delete()
	{
		if($this->input->post('deleteid')):
			$this->admin_model->removeEmail($this->input->post('deleteid'));
		    $success = "<strong> Email Template Deleted Successfully !! </strong>";
            $this->session->set_flashdata('flashSuccess', $success);
			redirect('/Emails');
		else:
			$this->data['form']=$this->admin_model->getEmail($this->uri->segment(4));
			$this->load->view('admin/email_delete.php', $this->data );
		endif;
	}
	
	
}